<?php

namespace App\Services\Implement;

use App\Models\JobRequirement;
use App\Models\User;
use App\Services\JobRequirementService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class JobRequirementImplement implements JobRequirementService
{
    public function authorizeAccess(User $user): void
    {
        if (!$user->canAccessRecruitment()) {
            throw new AuthorizationException('Recruitment access is not allowed.');
        }
    }

    public function getAll(User $user): Collection
    {
        $this->authorizeAccess($user);

        return JobRequirement::with('details')->latest()->get();
    }

    public function find(JobRequirement $jobRequirement, User $user): JobRequirement
    {
        $this->authorizeAccess($user);

        return $jobRequirement->load('details');
    }

    public function create(array $data, User $user): JobRequirement
    {
        $this->authorizeAccess($user);

        return DB::transaction(function () use ($data) {
            $jobRequirement = JobRequirement::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'footer' => $data['footer'] ?? null,
            ]);

            foreach ($data['requirements'] as $requirement) {
                $jobRequirement->details()->create([
                    'requirement' => $requirement,
                ]);
            }

            return $jobRequirement->load('details');
        });
    }

    public function update(JobRequirement $jobRequirement, array $data, User $user): JobRequirement
    {
        $this->authorizeAccess($user);

        return DB::transaction(function () use ($jobRequirement, $data) {
            $jobRequirement->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'footer' => $data['footer'] ?? null,
            ]);

            $jobRequirement->details()->delete();
            foreach ($data['requirements'] as $requirement) {
                $jobRequirement->details()->create([
                    'requirement' => $requirement,
                ]);
            }

            return $jobRequirement->load('details');
        });
    }

    public function setActive(JobRequirement $jobRequirement, bool $isActive, User $user): JobRequirement
    {
        $this->authorizeAccess($user);
        $jobRequirement->update(['is_active' => $isActive]);

        return $jobRequirement;
    }

    public function delete(JobRequirement $jobRequirement, User $user): void
    {
        $this->authorizeAccess($user);
        $jobRequirement->delete();
    }
}
