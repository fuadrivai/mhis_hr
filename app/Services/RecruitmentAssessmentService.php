<?php

namespace App\Services;

use App\Models\ApplicationAssessment;
use App\Models\CandidateApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

interface RecruitmentAssessmentService
{
    public function uploadAssessment(
        CandidateApplication $application,
        array $data,
        UploadedFile $file,
        User $user
    ): ApplicationAssessment;

    public function getAssessments(CandidateApplication $application): Collection;

    public function deleteAssessment(ApplicationAssessment $assessment, User $user): bool;

    public function streamAssessmentFile(ApplicationAssessment $assessment, User $user): Response;
}

