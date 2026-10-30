<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface CandidateRegistrationService
{
    public function getRegistrationPageData(User $user): array;

    public function getCandidateIndexData(User $user, array $filters = []): LengthAwarePaginator;

    public function getCandidateIndexFilterData(User $user): array;

    public function exportCandidateIndex(User $user, array $filters = []): StreamedResponse;

    public function getCandidateDetails(Candidate $candidate, User $user): Candidate;

    public function updateCandidate(Candidate $candidate, array $data, User $user): Candidate;

    public function updateCandidateApplication(
        Candidate $candidate,
        CandidateApplication $application,
        array $data,
        User $user
    ): CandidateApplication;

    public function getSelfRegistrationPageData(): array;

    public function getSources(): array;

    public function registerCandidate(
        array $data,
        string $registrationMethod,
        ?User $registeredBy = null,
        ?UploadedFile $resume = null
    ): CandidateApplication;

    public function previewImport(UploadedFile $file, User $user): array;

    public function commitImport(string $token, User $user): array;

    public function discardImportPreview(?string $token): void;

    public function getImportTemplateHeadings(): array;

    public function getResumePath(
        Candidate $candidate,
        CandidateApplication $application,
        User $user
    ): string;
}
