<?php

namespace App\Services\Implement;

use App\Models\JobRequirement;
use App\Models\Position;
use App\Models\User;
use App\Services\JobRequirementService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        return JobRequirement::with(['details', 'positions'])->latest()->get();
    }

    public function find(JobRequirement $jobRequirement, User $user): JobRequirement
    {
        $this->authorizeAccess($user);

        return $jobRequirement->load(['details', 'positions']);
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

            if (!empty($data['positions'])) {
                foreach ($data['positions'] as $posName) {
                    $trimmed = trim((string) $posName);
                    if ($trimmed !== '') {
                        $matchedPosition = Position::whereRaw('LOWER(name) = ?', [Str::lower($trimmed)])->first();
                        $jobRequirement->positions()->create([
                            'name' => $trimmed,
                            'position_id' => $matchedPosition?->id,
                        ]);
                    }
                }
            }

            foreach ($data['requirements'] as $requirement) {
                if (is_array($requirement)) {
                    $jobRequirement->details()->create([
                        'requirement'  => $requirement['text'] ?? ($requirement['requirement'] ?? ''),
                        'category'     => $requirement['category'] ?? 'other',
                        'is_mandatory' => !empty($requirement['is_mandatory']),
                        'weight'       => (int) ($requirement['weight'] ?? 3),
                    ]);
                } else {
                    $jobRequirement->details()->create([
                        'requirement'  => (string) $requirement,
                        'category'     => 'other',
                        'is_mandatory' => false,
                        'weight'       => 3,
                    ]);
                }
            }

            return $jobRequirement->load(['details', 'positions']);
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

            $jobRequirement->positions()->delete();
            if (!empty($data['positions'])) {
                foreach ($data['positions'] as $posName) {
                    $trimmed = trim((string) $posName);
                    if ($trimmed !== '') {
                        $matchedPosition = Position::whereRaw('LOWER(name) = ?', [Str::lower($trimmed)])->first();
                        $jobRequirement->positions()->create([
                            'name' => $trimmed,
                            'position_id' => $matchedPosition?->id,
                        ]);
                    }
                }
            }

            $jobRequirement->details()->delete();
            foreach ($data['requirements'] as $requirement) {
                if (is_array($requirement)) {
                    $jobRequirement->details()->create([
                        'requirement'  => $requirement['text'] ?? ($requirement['requirement'] ?? ''),
                        'category'     => $requirement['category'] ?? 'other',
                        'is_mandatory' => !empty($requirement['is_mandatory']),
                        'weight'       => (int) ($requirement['weight'] ?? 3),
                    ]);
                } else {
                    $jobRequirement->details()->create([
                        'requirement'  => (string) $requirement,
                        'category'     => 'other',
                        'is_mandatory' => false,
                        'weight'       => 3,
                    ]);
                }
            }

            return $jobRequirement->load(['details', 'positions']);
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
