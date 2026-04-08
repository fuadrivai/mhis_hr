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
            return redirect('/time/request')->with('success', 'Approval request submitted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['error' => $th->getMessage()]);
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
