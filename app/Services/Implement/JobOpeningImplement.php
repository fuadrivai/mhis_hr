<?php

namespace App\Services\Implement;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\JobOpening;
use App\Models\JobRequirement;
use App\Models\Position;
use App\Models\User;
use App\Services\JobOpeningService;
use App\Services\Recruitment\StageTransitionGuard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class JobOpeningImplement implements JobOpeningService
{
    public function getOpenings(array $filters, User $user, int $perPage = 25): LengthAwarePaginator
    {
        $this->authorizeRecruitment($user);

        $query = JobOpening::query()
            ->with(['branch', 'position', 'jobRequirement', 'creator', 'hiringManager.personal', 'hiringManager.user'])
            ->withCount([
                'applications as filled_count' => function ($q) {
                    $q->where('stage', 'joined');
                },
                'applications as active_count' => function ($q) {
                    $q->whereNotIn('stage', ['rejected', 'withdrawn']);
                },
                'applications as total_applications_count',
            ]);

        $this->applyBranchScope($query, $user);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('division', 'like', $search);
            });
        }

        return $query->latest('opened_at')->latest('id')->paginate($perPage)->withQueryString();
    }

    public function getOpeningDetails(JobOpening $opening, User $user): array
    {
        $this->authorizeRecruitment($user);
        $this->assertBranchAccess($opening->branch_id, $user);

        $opening->load([
            'branch',
            'position',
            'jobRequirement.details',
            'jobRequirement.positions',
            'creator',
            'hiringManager.personal',
            'hiringManager.user',
            'applications.candidate',
            'applications.hrPic',
            'applications.latestCvMatchResult.latestOverride',
            'applications.stageHistories.changer',
        ]);

        // Group applications by stage
        $stages = StageTransitionGuard::STAGES;
        $applicationsByStage = [];
        foreach (array_keys($stages) as $stageKey) {
            $applicationsByStage[$stageKey] = $opening->applications
                ->where('stage', $stageKey)
                ->values();
        }

        return [
            'opening'             => $opening,
            'stages'              => $stages,
            'applicationsByStage' => $applicationsByStage,
            'filledCount'         => $opening->filled_count,
            'activeCount'         => $opening->active_applications_count,
            'totalCount'          => $opening->applications->count(),
        ];
    }

    public function getFormData(User $user): array
    {
        $this->authorizeRecruitment($user);

        $branches = $this->accessibleBranches($user);

        $positions = Position::orderBy('name')->get(['id', 'name']);

        $jobRequirements = JobRequirement::where('is_active', true)
            ->with(['details', 'positions'])
            ->orderBy('title')
            ->get();

        $hiringManagers = Employee::where('is_active', true)
            ->whereHas('user')
            ->with(['user:id,name,email', 'personal:id,fullname,first_name,last_name'])
            ->get();

        return compact('branches', 'positions', 'jobRequirements', 'hiringManagers');
    }

    public function createOpening(array $data, User $user): JobOpening
    {
        $this->authorizeRecruitment($user);
        $this->assertBranchAccess((int) $data['branch_id'], $user);

        return DB::transaction(function () use ($data, $user) {
            return JobOpening::create([
                'job_requirement_id'        => $data['job_requirement_id'] ?? null,
                'position_id'               => $data['position_id'] ?? null,
                'title'                     => $data['title'],
                'division'                  => $data['division'] ?? null,
                'branch_id'                 => $data['branch_id'],
                'headcount'                 => max(1, (int) ($data['headcount'] ?? 1)),
                'hiring_manager_employee_id'=> $data['hiring_manager_employee_id'] ?? null,
                'opened_at'                 => $data['opened_at'],
                'closes_at'                 => $data['closes_at'] ?? null,
                'status'                    => $data['status'] ?? 'open',
                'created_by'                => $user->id,
            ]);
        });
    }

    public function updateOpening(JobOpening $opening, array $data, User $user): JobOpening
    {
        $this->authorizeRecruitment($user);
        $this->assertBranchAccess($opening->branch_id, $user);

        if (!empty($data['branch_id'])) {
            $this->assertBranchAccess((int) $data['branch_id'], $user);
        }

        return DB::transaction(function () use ($opening, $data) {
            $opening->update([
                'job_requirement_id'        => $data['job_requirement_id'] ?? $opening->job_requirement_id,
                'position_id'               => $data['position_id'] ?? $opening->position_id,
                'title'                     => $data['title'] ?? $opening->title,
                'division'                  => array_key_exists('division', $data) ? $data['division'] : $opening->division,
                'branch_id'                 => $data['branch_id'] ?? $opening->branch_id,
                'headcount'                 => isset($data['headcount']) ? max(1, (int) $data['headcount']) : $opening->headcount,
                'hiring_manager_employee_id'=> array_key_exists('hiring_manager_employee_id', $data) ? $data['hiring_manager_employee_id'] : $opening->hiring_manager_employee_id,
                'opened_at'                 => $data['opened_at'] ?? $opening->opened_at,
                'closes_at'                 => array_key_exists('closes_at', $data) ? $data['closes_at'] : $opening->closes_at,
                'status'                    => $data['status'] ?? $opening->status,
            ]);

            $opening->syncHeadcountStatus();

            return $opening->refresh();
        });
    }

    public function closeOpening(JobOpening $opening, ?string $reason, User $user): JobOpening
    {
        $this->authorizeRecruitment($user);
        $this->assertBranchAccess($opening->branch_id, $user);

        $opening->update([
            'status'        => 'closed',
            'closed_reason' => $reason,
        ]);

        return $opening->refresh();
    }

    public function reopenOpening(JobOpening $opening, User $user): JobOpening
    {
        $this->authorizeRecruitment($user);
        $this->assertBranchAccess($opening->branch_id, $user);

        $opening->update([
            'status'        => 'open',
            'closed_reason' => null,
        ]);

        $opening->syncHeadcountStatus();

        return $opening->refresh();
    }

    public function getOpeningsForDropdown(?int $branchId = null, ?User $user = null): Collection
    {
        $query = JobOpening::query()
            ->where('status', 'open')
            ->with(['branch', 'position']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($user) {
            $this->applyBranchScope($query, $user);
        }

        return $query->orderBy('title')->get();
    }

    private function authorizeRecruitment(User $user): void
    {
        if (!$user->canAccessRecruitment()) {
            throw new AuthorizationException('The user does not have permission to access the recruitment module.');
        }
    }

    private function branchScopeId(User $user): ?int
    {
        if ($user->isRecruitmentAdministrator() || $user->isRecruitmentMonitor()) {
            return null;
        }

        $branchId = $user->employee?->employment?->branch_id;
        if (!$branchId) {
            throw new AuthorizationException('The user does not have an assigned branch.');
        }

        return (int) $branchId;
    }

    private function accessibleBranches(User $user): Collection
    {
        $branches = Branch::where('is_active', true);
        $branchScopeId = $this->branchScopeId($user);

        if ($branchScopeId !== null) {
            $branches->whereKey($branchScopeId);
        }

        return $branches->orderBy('name')->get();
    }

    private function assertBranchAccess(int $branchId, User $user): void
    {
        $branchScopeId = $this->branchScopeId($user);

        if ($branchScopeId !== null && $branchScopeId !== $branchId) {
            throw new AuthorizationException('The user cannot access data from this branch.');
        }
    }

    private function applyBranchScope(Builder $query, User $user): void
    {
        $branchScopeId = $this->branchScopeId($user);

        if ($branchScopeId !== null) {
            $query->where('branch_id', $branchScopeId);
        }
    }
}

