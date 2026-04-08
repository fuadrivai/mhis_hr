<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\ApprovalHistory;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRule;
use App\Models\Employee;
use Illuminate\Support\Collection;

class ApprovalEngine
{
    /**
     * Resolve the approval rule for a requester employee.
     *
     * @param  \App\Models\Employee  $employee
     * @return \App\Models\ApprovalRule|null
     */
    public function resolveApprovalRule(Employee $employee): ?ApprovalRule
    {
        $employment = $employee->employment;
        if (!$employment) {
            return null;
        }

        $branchId = $employment->branch_id;
        $organizationId = $employment->organization_id;
        $jobLevelId = $employment->job_level_id;
        $positionId = $employment->job_position_id;

        if (!$branchId) {
            return null;
        }

        return ApprovalRule::with('steps')
            ->where('branch_id', $branchId)
            ->where(function ($query) use ($organizationId) {
                if ($organizationId !== null) {
                    $query->where('organization_id', $organizationId)->orWhereNull('organization_id');
                } else {
                    $query->whereNull('organization_id');
                }
            })
            ->where(function ($query) use ($jobLevelId) {
                if ($jobLevelId !== null) {
                    $query->where('job_level_id', $jobLevelId)->orWhereNull('job_level_id');
                } else {
                    $query->whereNull('job_level_id');
                }
            })
            ->where(function ($query) use ($positionId) {
                if ($positionId !== null) {
                    $query->where('position_id', $positionId)->orWhereNull('position_id');
                } else {
                    $query->whereNull('position_id');
                }
            })
            ->orderByRaw('(organization_id IS NOT NULL) DESC')
            ->orderByRaw('(job_level_id IS NOT NULL) DESC')
            ->orderByRaw('(position_id IS NOT NULL) DESC')
            ->first();
    }

    /**
     * Return grouped approval steps for the resolved approval rule.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Support\Collection
     */
    public function resolveApprovalSteps(Employee $employee): Collection
    {
        $rule = $this->resolveApprovalRule($employee);
        if (!$rule) {
            return collect();
        }

        return $rule->steps->groupBy('step_order')->map(function ($steps, $stepOrder) {
            return [
                'step_order' => (int) $stepOrder,
                'approval_mode' => $steps->first()->approval_mode,
                'approvers' => $steps->map(function ($step) {
                    return [
                        'approver_employee_id' => $step->approver_employee_id,
                        'step_name' => $step->name,
                    ];
                })->values(),
            ];
        })->values();
    }

public function approve(ApprovalRequest $approvalRequest, Approval $approval, string $action, ?string $note = null, ?int $performedByEmployeeId = null): ApprovalRequest
    {
        $allowedActions = ['approved', 'rejected', 'skipped', 'cancelled'];
        if (!in_array($action, $allowedActions)) {
            throw new \InvalidArgumentException('Invalid approval action provided.');
        }

        $approval->status = $action;
        $approval->note = $note;
        $approval->actioned_date = now();
        $approval->save();

        $approverEmployeeId = $performedByEmployeeId ?? $approval->approver_employee_id;

        ApprovalHistory::create([
            'approval_request_id' => $approvalRequest->id,
            'approval_id' => $approval->id,
            'step_order' => $approval->step_order,
            'approver_employee_id' => $approverEmployeeId,
            'action' => $action,
            'note' => $note,
        ]);

        if (in_array($action, ['rejected', 'cancelled'], true)) {
            $approvalRequest->update([
                'status' => $action,
                'current_step' => $approval->step_order,
            ]);
            return $approvalRequest;
        }

        $stepApprovals = $approvalRequest->approvals()->where('step_order', $approval->step_order)->get();

        if ($approval->approval_mode === 'any' && $action === 'approved') {
            $stepApprovals->where('status', 'pending')->each(function (Approval $pending) {
                $pending->update([
                    'status' => 'skipped',
                    'actioned_date' => now(),
                ]);
            });
        }

        if ($approval->approval_mode === 'all') {
            $pendingCount = $stepApprovals->where('status', 'pending')->count();
            if ($pendingCount > 0) {
                return $approvalRequest;
            }

            if ($stepApprovals->where('status', 'rejected')->isNotEmpty()) {
                $approvalRequest->update([
                    'status' => 'rejected',
                    'current_step' => $approval->step_order,
                ]);
                return $approvalRequest;
            }
        }

        $nextApproval = $approvalRequest->approvals()
            ->where('step_order', '>', $approval->step_order)
            ->orderBy('step_order')
            ->first();

        if (!$nextApproval) {
            $approvalRequest->update([
                'status' => 'approved',
                'current_step' => $approval->step_order,
            ]);
            return $approvalRequest;
        }

        $approvalRequest->update([
            'current_step' => $nextApproval->step_order,
            'status' => 'pending',
        ]);

        return $approvalRequest;
    }
}
