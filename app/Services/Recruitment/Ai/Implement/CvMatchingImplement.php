<?php

namespace App\Services\Recruitment\Ai\Implement;

use App\Models\CandidateApplication;
use App\Models\CvMatchOverride;
use App\Models\CvMatchRequirementResult;
use App\Models\CvMatchResult;
use App\Models\JobOpening;
use App\Models\JobRequirement;
use App\Models\JobRequirementPosition;
use App\Models\User;
use App\Jobs\ProcessCvMatchJob;
use App\Services\Recruitment\Ai\AiMatchingProvider;
use App\Services\Recruitment\Ai\CvMatchingService;
use App\Services\Recruitment\Ai\CvScoreCalculator;
use App\Services\Recruitment\Ai\CvTextExtractor;
use App\Services\Recruitment\Ai\PiiRedactor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

class CvMatchingImplement implements CvMatchingService
{
    public function __construct(
        private CvTextExtractor $extractor,
        private PiiRedactor $redactor,
        private AiMatchingProvider $provider,
        private CvScoreCalculator $calculator,
    ) {
    }

    /**
     * Process AI matching for a candidate application.
     */
    public function matchApplication(
        CandidateApplication $application,
        ?User $triggeredBy = null,
        bool $forceRerun = false
    ): CvMatchResult {
        // 1. Validate application has CV
        if (empty($application->resume_path)) {
            return $this->recordFailure(
                $application,
                'Candidate application has no CV file attached.',
                $triggeredBy
            );
        }

        // 2. Resolve file path on disk
        $cvFullPath = $this->resolveCvFilePath($application->resume_path);
        if (!$cvFullPath || !file_exists($cvFullPath)) {
            return $this->recordFailure(
                $application,
                "CV file not found on disk at: {$application->resume_path}",
                $triggeredBy
            );
        }

        // 3. Validate privacy consent version (must be candidate-privacy-v2)
        if ($application->consent_version !== 'candidate-privacy-v2') {
            return $this->recordFailure(
                $application,
                'Candidate has not consented to AI CV matching (consent version: candidate-privacy-v2 required).',
                $triggeredBy
            );
        }

        // 4. Resolve Job Opening and Job Requirement
        $opening = $application->jobOpening;
        $requirement = $this->resolveJobRequirement($application, $opening);

        if (!$requirement || $requirement->details->isEmpty()) {
            return $this->recordFailure(
                $application,
                'No job requirement criteria configured for this job opening.',
                $triggeredBy,
                $opening?->id,
                $requirement?->id
            );
        }

        // 5. Extract CV text
        try {
            $extractedText = $this->extractor->extractText($cvFullPath);
        } catch (Throwable $e) {
            Log::warning("CV text extraction failed for application #{$application->id}: " . $e->getMessage());
            $extractedText = '';
        }

        $cvHash = hash('sha256', $extractedText !== '' ? $extractedText : (string) @file_get_contents($cvFullPath));

        // Poor scan or unscannable PDF check (< 50 characters)
        if (mb_strlen(trim($extractedText)) < 50) {
            return $this->recordNeedsManualReview(
                $application,
                $opening?->id,
                $requirement->id,
                $cvHash,
                $triggeredBy,
                'CV text could not be extracted cleanly (poor scan, image-only PDF, or non-standard format). Please review candidate manually.'
            );
        }

        // 6. Build requirement details snapshot and hash
        $details = [];
        $detailMap = [];
        foreach ($requirement->details as $d) {
            $detailData = [
                'id'           => $d->id,
                'requirement'  => $d->requirement,
                'category'     => $d->category ?? 'other',
                'is_mandatory' => (bool) ($d->is_mandatory ?? false),
                'weight'       => (int) ($d->weight ?? 3),
            ];
            $details[] = $detailData;
            $detailMap[$d->id] = $detailData;
        }

        $reqHash = hash('sha256', json_encode($details));
        $promptVersion = (string) config('ai.prompt_version', 'v1');
        $model = $this->provider->getModelName();
        $providerName = $this->provider->getProviderName();

        // 7. Deterministic Cache check
        if (!$forceRerun) {
            $cached = CvMatchResult::where('candidate_application_id', $application->id)
                ->where('cv_hash', $cvHash)
                ->where('requirement_hash', $reqHash)
                ->where('prompt_version', $promptVersion)
                ->where('model', $model)
                ->where('status', 'completed')
                ->latest()
                ->first();

            if ($cached) {
                return $cached;
            }
        }

        // 8. Redact PII from CV
        $candidate = $application->candidate;
        $knownPii = array_filter([
            $candidate?->full_name,
            $candidate?->email,
            $candidate?->phone,
            $candidate?->current_city,
        ]);
        $redactedCv = $this->redactor->redact($extractedText, $knownPii);

        // 9. Call AI Matching Provider
        try {
            $aiResponse = $this->provider->matchCv($redactedCv, $details, $promptVersion);
        } catch (Throwable $e) {
            Log::error("AI matching provider exception for app #{$application->id}: " . $e->getMessage());
            return $this->recordFailure(
                $application,
                'AI service error: ' . $e->getMessage(),
                $triggeredBy,
                $opening?->id,
                $requirement->id
            );
        }

        // 10. Deterministic scoring and evidence verification
        $calc = $this->calculator->calculate($aiResponse['requirements'] ?? [], $details, $redactedCv);

        // 11. Persist result and requirement breakdowns in transaction
        return DB::transaction(function () use (
            $application,
            $opening,
            $requirement,
            $calc,
            $aiResponse,
            $details,
            $detailMap,
            $reqHash,
            $cvHash,
            $providerName,
            $model,
            $promptVersion,
            $triggeredBy
        ) {
            $matchResult = CvMatchResult::create([
                'candidate_application_id' => $application->id,
                'job_opening_id'           => $opening?->id,
                'job_requirement_id'       => $requirement->id,
                'status'                   => 'completed',
                'total_score'              => $calc['total_score'],
                'mandatory_missing'        => $calc['mandatory_missing'],
                'summary'                  => $aiResponse['summary'] ?? null,
                'red_flags'                => $aiResponse['red_flags'] ?? [],
                'interview_questions'      => $aiResponse['interview_questions'] ?? [],
                'requirement_snapshot'     => $details,
                'requirement_hash'         => $reqHash,
                'cv_hash'                  => $cvHash,
                'provider'                 => $providerName,
                'model'                    => $model,
                'prompt_version'           => $promptVersion,
                'error_message'            => null,
                'triggered_by'             => $triggeredBy?->id,
                'processed_at'             => now(),
            ]);

            foreach ($calc['results'] as $item) {
                $detailId = $item['id'] ?? null;
                $def = $detailMap[$detailId] ?? null;

                CvMatchRequirementResult::create([
                    'cv_match_result_id'        => $matchResult->id,
                    'job_requirement_detail_id' => $detailId,
                    'requirement_text'          => $def['requirement'] ?? ($item['requirement_text'] ?? 'Unknown requirement'),
                    'category'                  => $def['category'] ?? 'other',
                    'is_mandatory'              => (bool) ($def['is_mandatory'] ?? false),
                    'weight'                    => (int) ($def['weight'] ?? 3),
                    'status'                    => $item['status'] ?? 'unknown',
                    'evidence'                  => $item['evidence'] ?? null,
                    'evidence_verified'         => (bool) ($item['evidence_verified'] ?? false),
                    'reason'                    => $item['reason'] ?? null,
                ]);
            }

            return $matchResult->load(['requirementResults', 'latestOverride']);
        });
    }

    /**
     * Save HR score override with mandatory note.
     */
    public function overrideScore(
        CvMatchResult $result,
        ?int $score,
        string $note,
        User $user
    ): CvMatchOverride {
        $trimmedNote = trim($note);
        if ($trimmedNote === '') {
            throw new InvalidArgumentException('Note/reason is required when overriding AI match score.');
        }

        if ($score !== null && ($score < 0 || $score > 100)) {
            throw new InvalidArgumentException('Override score must be between 0 and 100.');
        }

        return CvMatchOverride::create([
            'cv_match_result_id' => $result->id,
            'override_score'     => $score,
            'note'               => $trimmedNote,
            'overridden_by'      => $user->id,
        ]);
    }

    /**
     * Extract identity info from an uploaded CV for form auto-filling.
     */
    public function parseCvFile(UploadedFile|string $file): array
    {
        $filePath = is_string($file) ? $file : $file->getRealPath();
        $text = $this->extractor->extractText($filePath);
        return $this->provider->parseCvIdentity($text);
    }

    /**
     * Batch process matching for all CV-bearing candidates in an opening.
     */
    public function batchMatchOpening(JobOpening $opening, User $user): array
    {
        $applications = $opening->applications()
            ->whereNotNull('resume_path')
            ->get();

        $dispatched = [];
        foreach ($applications as $app) {
            ProcessCvMatchJob::dispatch($app->id, $user->id, false);
            $dispatched[] = $app->id;
        }

        return $dispatched;
    }

    /**
     * Resolve the absolute path of a CV file on disk.
     */
    private function resolveCvFilePath(string $resumePath): ?string
    {
        if (file_exists($resumePath)) {
            return $resumePath;
        }

        if (Storage::disk('local')->exists($resumePath)) {
            return Storage::disk('local')->path($resumePath);
        }

        if (Storage::disk('public')->exists($resumePath)) {
            return Storage::disk('public')->path($resumePath);
        }

        return null;
    }

    /**
     * Resolve the JobRequirement instance for this candidate application.
     */
    private function resolveJobRequirement(CandidateApplication $application, ?JobOpening $opening): ?JobRequirement
    {
        // 1. From Job Opening requirement
        if ($opening && $opening->jobRequirement) {
            return $opening->jobRequirement;
        }

        // 2. From Position linked requirement
        $positionId = $opening?->position_id ?? $application->position_id;
        if ($positionId) {
            $link = JobRequirementPosition::where('position_id', $positionId)->first();
            if ($link && $link->jobRequirement) {
                return $link->jobRequirement;
            }
        }

        return null;
    }

    private function recordFailure(
        CandidateApplication $application,
        string $errorMessage,
        ?User $triggeredBy = null,
        ?int $openingId = null,
        ?int $requirementId = null
    ): CvMatchResult {
        return CvMatchResult::create([
            'candidate_application_id' => $application->id,
            'job_opening_id'           => $openingId ?? $application->job_opening_id,
            'job_requirement_id'       => $requirementId,
            'status'                   => 'failed',
            'error_message'            => $errorMessage,
            'requirement_hash'         => '',
            'cv_hash'                  => '',
            'provider'                 => $this->provider->getProviderName(),
            'model'                    => $this->provider->getModelName(),
            'prompt_version'           => (string) config('ai.prompt_version', 'v1'),
            'triggered_by'             => $triggeredBy?->id,
            'processed_at'             => now(),
        ]);
    }

    private function recordNeedsManualReview(
        CandidateApplication $application,
        ?int $openingId,
        int $requirementId,
        string $cvHash,
        ?User $triggeredBy,
        string $summary
    ): CvMatchResult {
        return CvMatchResult::create([
            'candidate_application_id' => $application->id,
            'job_opening_id'           => $openingId ?? $application->job_opening_id,
            'job_requirement_id'       => $requirementId,
            'status'                   => 'needs_manual_review',
            'summary'                  => $summary,
            'requirement_hash'         => '',
            'cv_hash'                  => $cvHash,
            'provider'                 => $this->provider->getProviderName(),
            'model'                    => $this->provider->getModelName(),
            'prompt_version'           => (string) config('ai.prompt_version', 'v1'),
            'triggered_by'             => $triggeredBy?->id,
            'processed_at'             => now(),
        ]);
    }
}

