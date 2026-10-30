<?php

namespace App\Services;

use App\Models\JobRequirement;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface JobRequirementService
{
    public function authorizeAccess(User $user): void;

    public function getAll(User $user): Collection;

    public function find(JobRequirement $jobRequirement, User $user): JobRequirement;

    public function create(array $data, User $user): JobRequirement;

    public function update(JobRequirement $jobRequirement, array $data, User $user): JobRequirement;

    public function setActive(JobRequirement $jobRequirement, bool $isActive, User $user): JobRequirement;

    public function delete(JobRequirement $jobRequirement, User $user): void;
}
