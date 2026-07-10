<?php

namespace App\Services\Implement;

use App\Mail\TimeoffMail;
use App\Models\Approval;
use App\Models\ApprovalHistory;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestAttachment;
use App\Models\ApprovalRequestData;
use App\Models\Employee;
use App\Models\Session;
use App\Models\TimeOff;
use App\Services\ApprovalEngine;
use App\Services\ApprovalRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use function App\Helpers\sendMessage;

class ApprovalRequestImplement implements ApprovalRequestService{
    private ApprovalEngine $approvalEngine;

    public function __construct(ApprovalEngine $approvalEngine)
    {
        $this->approvalEngine = $approvalEngine;
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
        try {
            return ApprovalRequest::find($id);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function post($request)
    {
        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($request['requester_employee_id']);
            $timeoff = TimeOff::findOrFail($request['timeoff_id']);
            $approvalRule = $this->approvalEngine->resolveApprovalRule($employee);

            if (!$approvalRule) {
                return back()->withErrors(['requester_employee_id' => 'No matching approval rule found for this employee.']);
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

            foreach ($request['attachments'] as $file) {
                $path = $file->store('approval-request-attachments');
                ApprovalRequestAttachment::create([
                    'approval_request_id' => $approvalRequest->id,
                    'field_name' => 'attachments',
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
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

            $approver = $approval->approver->load('user');
            $employee = $employee->load('personal');
            $personal = $employee->personal;
            $this->_sendNotification($approver->user->id, [
                    'title' => 'Approval Request Pending',
                    'body'  => 'You have a new time off request to approve for ' . $personal->fullname,
                ]);
            $this->_sendEmail($approvalRequest);
            
            DB::commit();
            return $approvalRequest;
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['error' => $th->getMessage()]);
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
        $request = ApprovalRequest::findOrFail($requestId);
        $approval = $request->approvals()->where('approver_employee_id', $employee->id)->firstOrFail()->load('approver.personal');
        $action = data_get($payload, 'action');
        $note = data_get($payload, 'note');
        if (!in_array($action, ['approved', 'rejected','cancelled'])) {
            throw new \Exception('Invalid action. Allowed values are: approved, rejected, cancelled');
        }

        $approval->status = $action ;
        $approval->note = $note;
        $approval->actioned_date = now();
        $approval->show_action = 0;
        $approval->save();

        if ($approval->status === 'rejected') {
            $request->status = 'rejected';
            $request->show_cancel = 0;
            $request->save();
            

            Approval::where('approval_request_id', $request->id)
                ->where('id', '!=', $approval->id)
                ->where('status', 'pending')
                ->update(['status' => 'skipped', 'show_action' => 0]);
            $this->_sendNotification($request->requester->user_id, [
                "title" => "Approval Request Rejected",
                "body" => "Your time off request has been rejected.",
            ]);
            $this->_sendEmail($request);
        } else if ($approval->status === 'cancelled') {
            $request->status = 'cancelled';
            $request->show_cancel = 0;
            $request->save();

            Approval::where('approval_request_id', $request->id)
                ->where('id', '!=', $approval->id)
                ->whereIn('status', ['pending'])
                ->update(['status' => 'skipped', 'show_action' => 0]);
        }else{
            $nextApproval = Approval::where('approval_request_id', $request->id)
                ->where('status', 'pending')
                ->orderBy('step_order')
                ->first();

            if (!$nextApproval) {
                $request->status = 'approved';
                $request->show_cancel = 0;
                $request->save();
                
                $this->_sendNotification($request->requester->user_id, [
                    "title" => "Approval Request Approved",
                    "body" => "Your time off request has been approved.",
                ]);
                $this->_sendEmail($request);
            } else {
                $nextApproval->show_action = 1;
                $nextApproval->save();
                $this->_sendNotification($nextApproval->approver->user_id, [
                    "title" => "Approval Request Pending",
                    "body" => "You have a new time off request to approve.",
                ]);
                $this->_sendEmail($request);
            }
        }

        ApprovalHistory::create([
            'approval_request_id' => $request->id,
            'action' => $action,
            'step_order' => $approval->step_order,
            'approver_employee_id' => $employee->id,
            'note' => "Time off request has been {$action}" . ($note ? " with note: {$note}" : ''),
        ]);

        return $approval;
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
            $approval = $approvalRequest->approvals()->where('status', 'pending')->first()->load('approver.personal');
            $requester = $approvalRequest->requester->load('personal');
            $timeoff = $approvalRequest->timeoff;
            $requestData = $approvalRequest->data;
            $email = $approval->approver->personal->email ?? null;
            if (!$email) {
                logger()->warning('Approver email not found', [
                    'approval_request_id' => $approvalRequest->id,
                    'approver_id' => $approvalRequest->approvals()->where('status', 'pending')->first()->approver->id,
                ]);
                return;
            }
            $data = [
                'approver_name' => $approval->approver->personal->name ?? null,
                'requester_name' => $requester->personal->name ?? null,
                'timeoff_name' => $timeoff->name ?? null,
                'timeoff_date'=> $requestData->payload['start_date'] ?? null,
                'reason' => $approval->note?? null,
                'remaining_balance'=>0,
                'approve_url'=>null,
                'reject_url'=>null,
                'subject'=>'Approval Request Pending',
                'template'=>'email-template.timeoff',
            ];
            Mail::to($email)->send(new TimeoffMail($data));
        } catch (\Exception $e) {
            logger()->error('Failed to send email notification', [
                'approval_request_id' => $approvalRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
