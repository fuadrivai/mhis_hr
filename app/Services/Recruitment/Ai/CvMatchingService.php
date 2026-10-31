<?php

namespace App\Services\Recruitment\Ai;

use App\Models\CandidateApplication;
use App\Models\CvMatchOverride;
use App\Models\CvMatchResult;
use App\Models\JobOpening;
use App\Models\User;
use Illuminate\Http\UploadedFile;

interface CvMatchingService
{
    /**
     * Process AI matching for a candidate application.
     */
    public function matchApplication(
        CandidateApplication $application,
        ?User $triggeredBy = null,
        bool $forceRerun = false
    ): CvMatchResult;

    /**
     * Save HR score override with mandatory note.
     */
    public function overrideScore(
        CvMatchResult $result,
        ?int $score,
        string $note,
        User $user
    ): CvMatchOverride;

    /**
     * Extract identity info from an uploaded CV for form auto-filling.
     */
    public function parseCvFile(UploadedFile|string $file): array;

    /**
     * Batch process matching for all CV-bearing candidates in an opening.
     */
    public function batchMatchOpening(JobOpening $opening, User $user): array;
}

