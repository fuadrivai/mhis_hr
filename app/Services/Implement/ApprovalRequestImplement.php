<?php

namespace App\Services\Implement;

use App\Models\Approval;
use App\Models\ApprovalHistory;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestAttachment;
use App\Models\ApprovalRequestData;
use App\Models\Employee;
use App\Models\TimeOff;
use App\Services\ApprovalEngine;
use App\Services\ApprovalRequestService;
use Illuminate\Support\Facades\DB;

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
            ]);

            ApprovalRequestData::create([
                'approval_request_id' => $approvalRequest->id,
                'data' => $request['dynamic_fields'] ?? [],
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
                ]);
            }

            ApprovalHistory::create([
                'approval_request_id' => $approvalRequest->id,
                'action' => 'submitted',
                'step_order' => 1,
            ]);

            DB::commit();
            return $approvalRequest;
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }

    public function getByUser($request)
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
                        $approvalRequestDataQuery->where("data->{$key}", $value);
                    }

                    if ($month !== null || $year !== null) {
                        $approvalRequestDataQuery->where(function ($dateQuery) use ($month, $year) {
                            $dateQuery->orWhere(function ($explicitQuery) use ($month, $year) {
                                if ($month !== null) {
                                    $explicitQuery->where('data->month', $month);
                                }

                                if ($year !== null) {
                                    $explicitQuery->where('data->year', $year);
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
                                            "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.date'))) <= ?",
                                            [$periodEnd]
                                        )
                                        ->whereRaw(
                                            "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.end_date')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.date'))) >= ?",
                                            [$periodStart]
                                        );
                                });
                            } elseif ($month !== null) {
                                $dateQuery
                                    ->orWhereRaw("MONTH(STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.start_date')), '%Y-%m-%d')) = ?", [$month])
                                    ->orWhereRaw("MONTH(STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.end_date')), '%Y-%m-%d')) = ?", [$month]);
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

    public function put($request)
    {
        // TODO: Implement put() method.
    }

    public function delete($id)
    {
        // TODO: Implement delete() method.
    }
}
