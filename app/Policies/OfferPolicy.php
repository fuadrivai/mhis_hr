<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OfferPolicy
{
    use HandlesAuthorization;

    /**
     * Strict compensation authorization gate:
     * 1. Recruitment Administrator
     * 2. Recruitment Monitor
     * 3. Approver assigned to the specific offer
     */
    public function viewCompensation(User $user, Offer $offer): bool
    {
        return $offer->canViewCompensation($user);
    }

    public function manage(User $user): bool
    {
        return $user->canAccessRecruitment();
    }

    public function approve(User $user, Offer $offer): bool
    {
        if (!$user->employee) {
            return false;
        }

        return $offer->canEmployeeApprove($user->employee->id);
    }
}

