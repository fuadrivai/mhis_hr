<?php

namespace App\Services\Implement;

use App\Mail\TimeoffMail;
use App\Models\Approval;
use App\Models\ApprovalHistory;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestAttachment;
use App\Models\ApprovalRequestData;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveAllocation;
use App\Models\LeaveAllocationHistory;
use App\Models\Session;
use App\Models\TimeOff;
use App\Models\User;
use App\Services\AcademicYearService;
use App\Services\ApprovalEngine;
use App\Services\ApprovalRequestService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

use function App\Helpers\prepareAttendance;
use function App\Helpers\sendMessage;

class ApprovalRequestImplement implements ApprovalRequestService{
    private ApprovalEngine $approvalEngine;
    private AcademicYearService $academicYearService;

    public function __construct(ApprovalEngine $approvalEngine, AcademicYearService $academicYearService)
    {
        $this->approvalEngine = $approvalEngine;
        $this->academicYearService = $academicYearService;
    }

    public function get($with = [])
    {
        try {
            return ApprovalRequest::with($with)->get();
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        return ApprovalRequest::with([
            'type',
            'data',
            'approval_rule',
            'approval_rule.steps',
            'approval_rule.branch',
            'approval_rule.organization',
            'approval_rule.level',
            'approval_rule.position',
            'requester.personal',
            'requester.employment',
            'approvals.approver.personal',
            'approvals.approver.employment',
            'approvals.approvalRequestData',
            'attachments',
            'histories.approver.personal',
        ])->findOrFail($id);
    }

    public function post($request)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($request['requester_employee_id']);
            $timeoff = TimeOff::findOrFail($request['timeoff_id']);
            $approvalRule = $this->approvalEngine->resolveApprovalRule($employee);
            $activeAcademicYear = $this->academicYearService->getActiveAcademicYear();

            if (!$approvalRule) {
                throw new \Exception('No matching approval rule found for this employee.');
            }

            if ($timeoff->deduct_leave_balance) {
                if (!$activeAcademicYear) {
                    throw new \Exception('No active academic year found for leave balance validation.');
                }

                $requestedDays = $this->getRequestedLeaveDays($request['dynamic_fields'] ?? []);

                $leaveAllocation = LeaveAllocation::where('employee_id', $employee->id)
                    ->where('timeoff_id', $timeoff->id)
                    ->where('academic_year_id', $activeAcademicYear->id)
                    ->whereColumn('total', '>', 'used')
                    ->where('remaining', '>', 0)
                    ->first();

                if (!$leaveAllocation) {
                    throw new \Exception('No available leave balance found for this timeoff in the active academic year. please contact HR for assistance.');
                }

                if ($requestedDays > $leaveAllocation->remaining) {
                    throw new \Exception("Requested leave days ({$requestedDays}) exceed the remaining leave balance ({$leaveAllocation->remaining}). please contact HR for assistance.");
                }

                $leaveAllocation->decrement('remaining', $requestedDays);
                $leaveAllocation->increment('used', $requestedDays);

                LeaveAllocationHistory::create([
                    'leave_allocation_id' => $leaveAllocation->id,
                    'type' => 'deduction',
                    'days' => $requestedDays,
                    'remark' => 'Leave balance deducted for approval request submission.',
                ]);
            }

            $approvalRequest = ApprovalRequest::create([
                'approval_rule_id' => $approvalRule->id,
                'requester_employee_id' => $employee->id,
                'timeoff_id' => $timeoff->id,
                'note' => $request['note'] ?? null,
                'current_step' => 1,
                'status' => 'pending',
                'show_cancel' => 1,
            ]);

            ApprovalRequestData::create([
                'approval_request_id' => $approvalRequest->id,
                'payload' => $request['dynamic_fields'] ?? [],
            ]);

            foreach ($request['attachments'] ?? [] as $file) {
                if (!$file->isValid()) {
                    throw new \Exception('Invalid attachment: '. $file->getErrorMessage());
                }

                $fileName = $file->hashName();
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getClientMimeType();
                $fileSize = $file->getSize();

                $dir = 'app/public/approval-request-attachments';
                $file->move(storage_path($dir),$fileName);

                ApprovalRequestAttachment::create([
                    'approval_request_id' =>$approvalRequest->id,
                    'field_name' =>'attachments',
                    'file_name' =>$originalName,
                    'file_path' => 'approval-request-attachments/' . $fileName,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                ]);
            }

            foreach ($approvalRule->steps as $step) {
                Approval::create([
                    'approval_request_id' => $approvalRequest->id,
                    'step_order' => $step->step_order,
                    'approver_employee_id' => $step->approver_employee_id,
                    'approval_mode' => $step->approval_mode,
                    'status' => 'pending',
                    'show_action' => 1,
                ]);
            }

            ApprovalHistory::create([
                'approval_request_id' => $approvalRequest->id,
                'action' => 'submitted',
                'step_order' => 1,
            ]);

            $approval = Approval::with('approver')
                ->where('approval_request_id', $approvalRequest->id)
                ->where('status', 'pending')
                ->orderBy('step_order')
                ->first();

            if (!$approval || !$approval->approver) {
                throw new \Exception('No pending approver found.');
            }

            $approver = $approval->approver->load('user');

            $employee->load('personal');

            $this->_sendNotification(
                $approver->user->id,
                [
                    'title' => 'Approval Request Pending',
                    'body' =>'You have a new time off request to approve for '. $employee->personal->fullname,
                ]
            );
            $this->_sendEmail($approvalRequest);
            DB::commit();
            return $approvalRequest;
        } catch (\Throwable $th) {
            DB::rollBack();
            logger()->error(
                'Approval request failed',
                [
                    'error' => $th->getMessage(),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                    'trace' => $th->getTraceAsString(),
                ]
            );
            throw $th;
        }
    }

    public function getRequestByUser($request)
    {
        try {
            $payload = is_array($request) ? $request : $request->all();
            $userId = data_get($payload, 'user.id') ?? auth()->id();

            if (!$userId) {
                throw new \Exception('User ID is required');
            }

            $employee = Employee::where('user_id', $userId)->first();

            if (!$employee) {
                throw new \Exception('Employee not found for the given user ID');
            }

            $month = data_get($payload, 'month');
            $year = data_get($payload, 'year');

            $month = is_numeric($month) ? (int) $month : null;
            $year = is_numeric($year) ? (int) $year : null;

            $dynamicFilters = collect($payload)
                ->except(['user', 'month', 'year'])
                ->filter(fn ($value) => is_scalar($value) && $value !== '')
                ->toArray();

            $query = ApprovalRequest::where('requester_employee_id', $employee->id);

            if (!empty($dynamicFilters) || $month !== null || $year !== null) {
                $query->whereHas('data', function ($approvalRequestDataQuery) use ($dynamicFilters, $month, $year) {
                    foreach ($dynamicFilters as $key => $value) {
                        $approvalRequestDataQuery->where("payload->{$key}", $value);
                    }

                    if ($month !== null || $year !== null) {
                        $approvalRequestDataQuery->where(function ($dateQuery) use ($month, $year) {
                            $dateQuery->orWhere(function ($explicitQuery) use ($month, $year) {
                                if ($month !== null) {
                                    $explicitQuery->where('payload->month', $month);
                                }

                                if ($year !== null) {
                                    $explicitQuery->where('payload->year', $year);
                                }
                            });

                            // Supports date-based payloads with start_date/end_date.
                            if ($year !== null) {
                                $periodStart = $month !== null
                                    ? \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString()
                                    : \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();

                                $periodEnd = $month !== null
                                    ? \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString()
                                    : \Carbon\Carbon::createFromDate($year, 12, 1)->endOfYear()->toDateString();

                                $dateQuery->orWhere(function ($rangeQuery) use ($periodStart, $periodEnd) {
                                    $rangeQuery
                                        ->whereRaw(
                                            "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date'))) <= ?",
                                            [$periodEnd]
                                        )
                                        ->whereRaw(
                                            "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.end_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date'))) >= ?",
                                            [$periodStart]
                                        );
                                });
                            } elseif ($month !== null) {
                                $dateQuery
                                    ->orWhereRaw("MONTH(STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), '%Y-%m-%d')) = ?", [$month])
                                    ->orWhereRaw("MONTH(STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.end_date')), '%Y-%m-%d')) = ?", [$month]);
                            }
                        });
                    }
                });
            }

            return $query->get();
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    public function getApprovalByUser($request)
    {
        try {
            $payload = is_array($request) ? $request : $request->all();
            $userId = data_get($payload, 'user.id') ?? auth()->id();

            if (!$userId) {
                throw new \Exception('User ID is required');
            }

            $employee = Employee::where('user_id', $userId)->first();

            if (!$employee) {
                throw new \Exception('Employee not found for the given user ID');
            }

            $month = data_get($payload, 'month');
            $year = data_get($payload, 'year');

            $month = is_numeric($month) ? (int) $month : null;
            $year = is_numeric($year) ? (int) $year : null;

            $dynamicFilters = collect($payload)
                ->except(['user', 'month', 'year'])
                ->filter(fn ($value) => is_scalar($value) && $value !== '')
                ->toArray();

            $query = Approval::where('approver_employee_id', $employee->id)
                // ->where('status', 'pending')
                ->whereHas('approvalRequest', function ($approvalRequestQuery) use ($dynamicFilters, $month, $year) {
                    foreach ($dynamicFilters as $key => $value) {
                        $approvalRequestQuery->whereHas('data', function ($approvalRequestDataQuery) use ($key, $value) {
                            $approvalRequestDataQuery->where("payload->{$key}", $value);
                        });
                    }

                    if ($month !== null || $year !== null) {
                        $approvalRequestQuery->whereHas('data', function ($approvalRequestDataQuery) use ($month, $year) {
                            $approvalRequestDataQuery->where(function ($dateQuery) use ($month, $year) {
                                if ($month !== null && $year !== null) {
                                    $periodStart = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
                                    $periodEnd = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

                                    $dateQuery->whereRaw(
                                        "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date'))) <= ?",
                                        [$periodEnd]
                                    )->whereRaw(
                                        "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.end_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date'))) >= ?",
                                        [$periodStart]
                                    );
                                } elseif ($year !== null) {
                                    $periodStart = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
                                    $periodEnd = \Carbon\Carbon::createFromDate($year, 12, 1)->endOfYear()->toDateString();

                                    $dateQuery->whereRaw(
                                        "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date'))) <= ?",
                                        [$periodEnd]
                                    )->whereRaw(
                                        "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.end_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date'))) >= ?",
                                        [$periodStart]
                                    );
                                } elseif ($month !== null) {
                                    $dateQuery
                                        ->orWhereRaw("MONTH(STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), '%Y-%m-%d')) = ?", [$month])
                                        ->orWhereRaw("MONTH(STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.end_date')), '%Y-%m-%d')) = ?", [$month]);
                                }
                            });
                        });
                    }
                });

            return $query->get();
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    public function action($data)
    {
        DB::beginTransaction();

        try {
            $payload = is_array($data)? $data : $data->all();
            $userId = data_get($payload, 'user.id')?? auth()->id();
            if (!$userId) {
                throw new \Exception('User ID is required');
            }

            $user = User::with('roles')->findOrFail($userId);
            $employee = Employee::where('user_id',$userId)->first();

            if (!$employee) {
                throw new \Exception('Employee not found for the given user ID');
            }

            $requestId = data_get($payload,'request_id');
            $request = ApprovalRequest::findOrFail($requestId);

            $action = data_get($payload,'action');
            $note = data_get($payload,'note');

            if (!in_array($action, ['approved','rejected','cancelled'])) {
                throw new \Exception('Invalid action.');
            }

            $isAdmin = $user->hasRole('admin') || $user->roles->contains('id', 1);

            $approvalQuery = $request->approvals()
                ->where('status', 'pending')
                ->where('show_action', 1);

            if (!$isAdmin || !in_array($action, ['approved', 'rejected'], true)) {
                $approvalQuery->where('approver_employee_id', $employee->id);
            }

            $approval = $approvalQuery->orderBy('step_order')->first();

            if (!$approval) {
                throw new \Exception('You are not authorized to act on this request, or it no longer has a pending approval.');
            }

            $approval->load('approver.personal');

            $approval->status = $action;
            $approval->note = $note;
            $approval->actioned_date = now();
            $approval->show_action = 0;
            $approval->save();

            if ($action === 'rejected') {

                $request->status = 'rejected';
                $request->show_cancel = 0;
                $request->save();

                Approval::where('approval_request_id', $request->id)
                    ->where('id','!=',$approval->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'skipped',
                        'show_action' => 0,
                    ]);

                $this->restoreLeaveBalance($request);
                $this->_sendNotification($request->requester->user_id,
                    [
                        'title' =>
                            'Approval Request Rejected',

                        'body' =>
                            'Your time off request has been rejected by ' . $approval->approver->personal->fullname . '.',
                    ]
                );

                $request['status'] ='rejected';
                $request['approver_name'] =$user->name ?? $approval->approver->personal->fullname ?? null;
                $request['approver_note'] = $approval->note ?? null;
                $this->_sendEmailToRequester($request);
            }
            elseif ($action === 'cancelled') {
                $request->status ='cancelled';
                $request->show_cancel = 0;
                $request->save();

                Approval::where('approval_request_id',$request->id)
                    ->where('id','!=',$approval->id)
                    ->where('status','pending')
                    ->update(['status' => 'skipped','show_action' => 0]);
                $this->restoreLeaveBalance($request);

                    $startDate = data_get($request->data->payload ?? [], 'start_date');
                    $endDate = data_get($request->data->payload ?? [], 'end_date')?? $startDate;
                    $dateDiff = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

                    for ($i = 0; $i < $dateDiff; $i++) {
                        $date = Carbon::parse($startDate)->addDays($i)->toDateString();
                        $attendance = Attendance::where('employee_id', $request->requester_employee_id)
                            ->where('date', $date)
                            ->first();
                        if ($attendance) {
                            $request->attendances()->detach($attendance->id);
                        } 
                    }
            }
            else {
                $nextApproval = Approval::where('approval_request_id',$request->id)
                    ->where('status','pending')
                    ->orderBy('step_order')
                    ->first();

                if (!$nextApproval) {
                    $request->current_step = $approval->step_order;
                    $request->status = 'approved';
                    $request->show_cancel = 0;
                    $request->save();

                    $this->_sendNotification($request->requester->user_id,
                        [
                            'title' =>'Approval Request Approved',
                            'body' => 'Your time off request has been approved by ' . $approval->approver->personal->fullname . '.',
                        ]
                    );

                    $request['status'] = 'approved';
                    $request['approver_name'] = $user->name ?? $approval->approver->personal->fullname ?? null;
                    $request['approver_note'] = $approval->note ?? null;
                    $this->_sendEmailToRequester($request);

                    $startDate = data_get($request->data->payload ?? [], 'start_date');
                    $endDate = data_get($request->data->payload ?? [], 'end_date')?? $startDate;
                    $dateDiff = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

                    for ($i = 0; $i < $dateDiff; $i++) {
                        $date = Carbon::parse($startDate)->addDays($i)->toDateString();
                        $attendance = Attendance::where('employee_id', $request->requester_employee_id)
                            ->where('date', $date)
                            ->first();
                        if ($attendance) {
                            $request->attendances()->syncWithoutDetaching([$attendance->id]);
                        } else {
                            $_requester = Employee::with(['personal','user', 'activeSchedule'])->where( 'id', $request->requester_employee_id)->first();

                            [$attendance, $attendanceDate] = prepareAttendance($_requester, $_requester->user, Carbon::parse($date));
                            $request->attendances()->syncWithoutDetaching([$attendance->id]);
                        }
                    }

                } else {
                    $request->current_step = $nextApproval->step_order;
                    $request->save();
                    $nextApproval->show_action =1;
                    $nextApproval->save();
                }
            }

            ApprovalHistory::create([
                'approval_request_id' =>$request->id,
                'action' => $action,
                'step_order' => $approval->step_order,
                'approver_employee_id' => $employee->id,
                'note' =>
                    "Time off request has been {$action}"
                    . (
                        $note
                            ? " with note: {$note}"
                            : ''
                    ),
            ]);
            DB::commit();
            return $approval;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cancel($data){
        $payload = is_array($data) ? $data : $data->all();
        $userId = data_get($payload, 'user.id') ?? auth()->id();
        if (!$userId) {
            throw new \Exception('User ID is required');
        }
        $employee = Employee::where('user_id', $userId)->first();
        if (!$employee) {
            throw new \Exception('Employee not found for the given user ID');
        }

        $requestId = data_get($payload, 'request_id');
        $note = data_get($payload, 'note');
        $request = ApprovalRequest::findOrFail($requestId);

        $request->status = 'cancelled';
        $request->show_cancel = 0;
        $request->save();

        Approval::where('approval_request_id', $request->id)
                ->whereIn('status', ['pending'])
                ->update(['status' => 'skipped', 'show_action' => 0]);

        $this->restoreLeaveBalance($request);

        ApprovalHistory::create([
            'approval_request_id' => $request->id,
            'action' => $request->status,
            'step_order' => $request->approvals()->max('step_order') + 1,
            'approver_employee_id' => $employee->id,
            'note' => "Time off request has been {$request->status}" . ($note ? " with note: {$note}" : ''),
        ]);
        return $request;
    }

    public function put($request)
    {
        // TODO: Implement put() method.
    }

    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    private function getRequestedLeaveDays(array $dynamicFields): int
    {
        $startDate = data_get($dynamicFields, 'start_date');
        $endDate = data_get($dynamicFields, 'end_date') ?? $startDate;

        if (!$startDate) {
            throw new \Exception('A start date is required to validate the leave balance.');
        }

        try {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $end = \Carbon\Carbon::parse($endDate)->startOfDay();
        } catch (\Throwable $th) {
            throw new \Exception('The leave request dates are invalid.');
        }

        if ($end->lt($start)) {
            throw new \Exception('The leave end date cannot be before the start date.');
        }

        return $start->diffInDays($end) + 1;
    }

    private function restoreLeaveBalance(ApprovalRequest $approvalRequest): void
    {
        if (!$approvalRequest->type->deduct_leave_balance) {
            return;
        }

        $activeAcademicYear = $this->academicYearService->getActiveAcademicYear();

        if (!$activeAcademicYear) {
            throw new \Exception('No active academic year found for leave balance restoration.');
        }

        $requestedDays = $this->getRequestedLeaveDays($approvalRequest->data->payload ?? []);

        $leaveAllocation = LeaveAllocation::where('employee_id', $approvalRequest->requester_employee_id)
            ->where('timeoff_id', $approvalRequest->timeoff_id)
            ->where('academic_year_id', $activeAcademicYear->id)
            ->firstOrFail();

        $leaveAllocation->increment('remaining', $requestedDays);
        $leaveAllocation->decrement('used', $requestedDays);

        LeaveAllocationHistory::create([
            'leave_allocation_id' => $leaveAllocation->id,
            'type' => 'restoration',
            'days' => $requestedDays,
            'remark' => "Leave balance restored after request {$approvalRequest->status}.",
        ]);
    }

    private function _sendNotification($userId, $data)
    {
        $sessions = Session::where('user_id', $userId)
                ->whereIn('device', ['android'])
                ->get();
        foreach ($sessions as $session) {
            if (empty($session->device_id)) {
                continue;
            }
            $result = sendMessage($session->device_id, $data);
            logger()->info('FCM Send Result', [
                'session_id'   => $session->id,
                'device'       => $session->device,
                'device_token' => $session->device_id,
                'result'       => $result,
            ]);
        }
    }

    private function _sendEmail($approvalRequest)
    {
        try {
            $requester = $approvalRequest->requester->load('personal');
            $timeoff = $approvalRequest->type;
            $requestData = $approvalRequest->data;
            $startDate = data_get($requestData, 'payload.start_date');
            $formattedStartDate = null;
            if (!empty($startDate)) {
                try {
                    $formattedStartDate = \Carbon\Carbon::parse($startDate)->format('D, M d, Y');
                } catch (\Throwable $th) {
                    $formattedStartDate = $startDate;
                }
            }

            $pendingApproval = $approvalRequest->approvals()
                ->where('status', 'pending')
                ->orderBy('step_order')
                ->first();

            if (!$pendingApproval) {
                return;
            }

            $approval = $pendingApproval->load('approver.personal');
            $email = $approval->approver->personal->email ?? null;
            if (!$email) {
                logger()->warning('Approver email not found', [
                    'approval_request_id' => $approvalRequest->id,
                    'approver_id' => $approval->approver->id ?? null,
                ]);
                return;
            }

            $expiresAt = now()->addDays(7);
            $data = [
                'approver_name' => $approval->approver->personal->fullname ?? null,
                'requester_name' => $requester->personal->fullname ?? null,
                'timeoff_name' => $timeoff->name ?? null,
                'timeoff_date'=> $formattedStartDate,
                'reason' => $approvalRequest->note?? null,
                'approve_url' => URL::temporarySignedRoute('api.time.request.email-action', $expiresAt, [
                    'user_id' => $approval->approver->user_id,
                    'request_id' => $approvalRequest->id,
                    'action' => 'approved',
                    'note' => null,
                ]),
                'reject_url' => URL::temporarySignedRoute('api.time.request.email-action', $expiresAt, [
                    'user_id' => $approval->approver->user_id,
                    'request_id' => $approvalRequest->id,
                    'action' => 'rejected',
                    'note' => null,
                ]),
                'subject'=>'Approval Request Pending',
                'template'=>'email-template.timeoff-request',
            ];
            Mail::mailer('smtp')->to($email)->send(new TimeoffMail($data));
        } catch (\Exception $e) {
            logger()->error('Failed to send email notification', [
                'approval_request_id' => $approvalRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function _sendEmailToRequester($approvalRequest)
    {
        try {
            $requester = $approvalRequest->requester->load('personal');
            $timeoff = $approvalRequest->type;
            $requestData = $approvalRequest->data;
            $startDate = data_get($requestData, 'payload.start_date');
            $formattedStartDate = null;
            if (!empty($startDate)) {
                try {
                    $formattedStartDate = \Carbon\Carbon::parse($startDate)->format('D, M d, Y');
                } catch (\Throwable $th) {
                    $formattedStartDate = $startDate;
                }
            }

            $email = $requester->personal->email ?? null;
            if (!$email) {
                logger()->warning('Requester email not found', [
                    'approval_request_id' => $approvalRequest->id,
                    'requester_id' => $requester->id ?? null,
                ]);
                return;
            }

            $data = [
                'requester_name' => $requester->personal->fullname ?? null,
                'approver_name' => $approvalRequest['approver_name'] ?? null,
                'reason' => $approvalRequest['approver_note'] ?? null,
                'timeoff_name' => $timeoff->name ?? null,
                'timeoff_date'=> $formattedStartDate,
                'subject'=>'Approval Request ' . $approvalRequest->status,
                'template'=>'email-template.timeoff-approved-rejected',
                'status'=> $approvalRequest->status,
            ];
            Mail::mailer('smtp')->to($email)->send(new TimeoffMail($data));
        } catch (\Exception $e) {
            logger()->error('Failed to send email notification to requester', [
                'approval_request_id' => $approvalRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
