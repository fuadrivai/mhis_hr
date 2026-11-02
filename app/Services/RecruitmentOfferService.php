<?php

namespace App\Services;

use App\Models\CandidateApplication;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Collection;

interface RecruitmentOfferService
{
    public function createOffer(CandidateApplication $application, array $data, User $user): Offer;

    public function updateOffer(Offer $offer, array $data, User $user): Offer;

    public function submitForApproval(Offer $offer, User $user): Offer;

    public function approveStep(Offer $offer, int $employeeId, ?string $comment, User $user): Offer;

    public function rejectStep(Offer $offer, int $employeeId, string $comment, User $user): Offer;

    public function markAsSent(Offer $offer, array $data, User $user): Offer;

    public function recordCandidateResponse(Offer $offer, string $status, ?string $declineReason, User $user): Offer;

    public function cancelOffer(Offer $offer, ?string $reason, User $user): Offer;

    public function getMyPendingApprovals(User $user): Collection;
}

