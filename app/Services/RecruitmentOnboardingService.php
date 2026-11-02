<?php

namespace App\Services;

use App\Models\ApplicationOnboarding;
use App\Models\CandidateApplication;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;

interface RecruitmentOnboardingService
{
    public function updateContract(
        CandidateApplication $application,
        array $data,
        ?UploadedFile $contractFile,
        User $user
    ): ApplicationOnboarding;

    public function updateJoinStatus(
        CandidateApplication $application,
        array $data,
        User $user
    ): ApplicationOnboarding;

    public function createEmployeeDraft(
        CandidateApplication $application,
        User $user
    ): Employee;

    public function getContractFilePath(ApplicationOnboarding $onboarding): ?string;
}

