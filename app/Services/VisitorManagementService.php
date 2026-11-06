<?php

namespace App\Services;

use App\Models\User;
use App\Models\VisitorVisit;

interface VisitorManagementService
{
    public function createVisit(User $user, array $data, string $method): array;

    public function processApproval(User $user, VisitorVisit $visit, string $decision, ?string $notes): VisitorVisit;

    public function checkIn(User $user, VisitorVisit $visit, array $data): VisitorVisit;

    public function checkOut(User $user, VisitorVisit $visit, array $data): VisitorVisit;

    public function correctCheckOut(User $user, VisitorVisit $visit, string $reason): VisitorVisit;

    public function cancelVisit(User $user, VisitorVisit $visit, string $reason): VisitorVisit;
}
