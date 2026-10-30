<?php

namespace App\Services;

use App\Models\RecruitmentMonitor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface RecruitmentMonitorService
{
    public function getMonitorPageData(User $user): array;

    public function addMonitor(int $employeeId, User $assignedBy): RecruitmentMonitor;

    public function removeMonitor(RecruitmentMonitor $monitor, User $user): void;
}
