<?php

namespace App\Services;

use App\Models\JobOpening;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface JobOpeningService
{
    public function getOpenings(array $filters, User $user, int $perPage = 25): LengthAwarePaginator;

    public function getOpeningDetails(JobOpening $opening, User $user): array;

    public function getFormData(User $user): array;

    public function createOpening(array $data, User $user): JobOpening;

    public function updateOpening(JobOpening $opening, array $data, User $user): JobOpening;

    public function closeOpening(JobOpening $opening, ?string $reason, User $user): JobOpening;

    public function reopenOpening(JobOpening $opening, User $user): JobOpening;

    public function getOpeningsForDropdown(?int $branchId = null, ?User $user = null): Collection;
}

