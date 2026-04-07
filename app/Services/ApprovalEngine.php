<?php

namespace App\Services;

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
}
