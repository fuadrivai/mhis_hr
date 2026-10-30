<?php

namespace App\Services\Implement;

use App\Models\Employee;
use App\Models\RecruitmentMonitor;
use App\Models\User;
use App\Services\RecruitmentMonitorService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecruitmentMonitorImplement implements RecruitmentMonitorService
{
    public function getMonitorPageData(User $user): array
    {
        $this->authorizeManagement($user);

        $monitors = RecruitmentMonitor::whereHas('employee', function ($query) {
            $query->where('is_active', 1);
        })
            ->with(['employee.user', 'employee.personal', 'assignedBy'])
            ->latest()
            ->get();
    
        $availableEmployees = Employee::query()
            ->whereNotIn('id', $monitors->pluck('employee_id'))
            ->where('is_active', 1)
            ->whereHas('user')
            ->with(['user:id,name,email', 'personal:id,fullname'])
            ->orderBy('id')
            ->get();

        return compact('monitors', 'availableEmployees');
    }

    public function addMonitor(int $employeeId, User $assignedBy): RecruitmentMonitor
    {
        $this->authorizeManagement($assignedBy);

        $employee = Employee::query()
            ->whereKey($employeeId)
            ->where('is_active', true)
            ->whereHas('user')
            ->first();

        if (!$employee) {
            throw ValidationException::withMessages([
                'employee_id' => 'The selected employee must be active and have a user account.',
            ]);
        }

        if (RecruitmentMonitor::where('employee_id', $employeeId)->exists()) {
            throw ValidationException::withMessages([
                'employee_id' => 'This employee is already assigned as a recruitment monitor.',
            ]);
        }

        return DB::transaction(fn () => RecruitmentMonitor::create([
            'employee_id' => $employeeId,
            'assigned_by' => $assignedBy->id,
        ]));
    }

    public function removeMonitor(RecruitmentMonitor $monitor, User $user): void
    {
        $this->authorizeManagement($user);
        $monitor->delete();
    }

    private function authorizeManagement(User $user): void
    {
        if (!$user->isRecruitmentAdministrator()) {
            throw new AuthorizationException('Only recruitment administrators can manage monitors.');
        }
    }
}
