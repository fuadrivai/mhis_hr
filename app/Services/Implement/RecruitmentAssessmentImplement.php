<?php

namespace App\Services\Implement;

use App\Models\ApplicationAssessment;
use App\Models\CandidateApplication;
use App\Models\InterviewRound;
use App\Models\User;
use App\Services\RecruitmentAssessmentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RecruitmentAssessmentImplement implements RecruitmentAssessmentService
{
    public function uploadAssessment(
        CandidateApplication $application,
        array $data,
        ?UploadedFile $file,
        User $user
    ): ApplicationAssessment {
        $this->ensureCanManageAssessments($user, $application);

        $documentUrl = !empty($data['document_url']) ? trim($data['document_url']) : null;
        if ($documentUrl && !preg_match('~^https?://~i', $documentUrl)) {
            $documentUrl = 'https://' . $documentUrl;
        }

        if (!$file && empty($documentUrl)) {
            throw ValidationException::withMessages([
                'document_url' => 'Please provide a document link (Google Docs, Google Drive, PDF link) or upload a PDF report.',
            ]);
        }

        $storedPath = null;
        $originalFilename = null;

        if ($file) {
            // Validate MIME type strictly PDF
            $mime = strtolower((string) $file->getMimeType());
            $ext = strtolower((string) $file->getClientOriginalExtension());
            if ($ext !== 'pdf' || !str_contains($mime, 'pdf')) {
                throw ValidationException::withMessages([
                    'assessment_file' => 'The psychotest assessment report must be a valid PDF file.',
                ]);
            }

            // Validate max 10MB
            $maxBytes = 10 * 1024 * 1024;
            if ($file->getSize() > $maxBytes) {
                throw ValidationException::withMessages([
                    'assessment_file' => 'The psychotest PDF file size must not exceed 10 MB.',
                ]);
            }

            // Store securely on private disk with UUID filename
            $storedFilename = Str::uuid()->toString() . '.pdf';
            $storedPath = $file->storeAs('recruitment/assessments', $storedFilename, 'local');
            $originalFilename = $file->getClientOriginalName();
        }

        $conclusion = $data['conclusion'] ?? null;
        if (!in_array($conclusion, ['recommended', 'considered', 'not_recommended'], true)) {
            throw ValidationException::withMessages([
                'conclusion' => 'Please select a valid conclusion (Recommended, Considered, or Not Recommended).',
            ]);
        }

        $assessedAt = $data['assessed_at'] ?? date('Y-m-d');

        return DB::transaction(function () use ($application, $data, $storedPath, $originalFilename, $documentUrl, $user, $conclusion, $assessedAt) {
            return ApplicationAssessment::create([
                'candidate_application_id' => $application->id,
                'type'                     => $data['type'] ?? 'psychotest',
                'assessed_at'              => $assessedAt,
                'conclusion'               => $conclusion,
                'notes'                    => $data['notes'] ?? null,
                'document_url'             => $documentUrl,
                'file_path'                => $storedPath,
                'original_filename'        => $originalFilename,
                'uploaded_by'              => $user->id,
            ]);
        });
    }

    public function getAssessments(CandidateApplication $application): Collection
    {
        return $application->assessments()
            ->with('uploadedBy')
            ->orderBy('assessed_at', 'desc')
            ->get();
    }

    public function deleteAssessment(ApplicationAssessment $assessment, User $user): bool
    {
        $this->ensureCanManageAssessments($user, $assessment->candidateApplication);

        return DB::transaction(function () use ($assessment) {
            if ($assessment->file_path && Storage::disk('local')->exists($assessment->file_path)) {
                Storage::disk('local')->delete($assessment->file_path);
            }
            return (bool) $assessment->delete();
        });
    }

    public function streamAssessmentFile(ApplicationAssessment $assessment, User $user): Response
    {
        $this->ensureCanViewAssessment($user, $assessment);

        if ($assessment->document_url) {
            return redirect()->away($assessment->document_url);
        }

        if (!$assessment->file_path || !Storage::disk('local')->exists($assessment->file_path)) {
            abort(404, 'Assessment file not found on private storage.');
        }

        $fullPath = Storage::disk('local')->path($assessment->file_path);
        $mime = mime_content_type($fullPath) ?: 'application/pdf';
        $filename = $assessment->original_filename ?: 'psychotest-report.pdf';

        return response()->file($fullPath, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
        ]);
    }

    protected function ensureCanManageAssessments(User $user, CandidateApplication $application): void
    {
        if (!$user->canAccessRecruitment()) {
            throw new AuthorizationException('You are not authorized to manage psychotest assessments.');
        }

        if (!$user->isRecruitmentAdministrator() && !$user->isRecruitmentMonitor()) {
            $branchScope = $user->branchScopeId();
            if ($branchScope && $application->branch_id && (int) $application->branch_id !== (int) $branchScope) {
                throw new AuthorizationException('Access denied for candidates outside your branch.');
            }
        }
    }

    protected function ensureCanViewAssessment(User $user, ApplicationAssessment $assessment): void
    {
        // 1. Full recruitment access
        if ($user->canAccessRecruitment()) {
            if (!$user->isRecruitmentAdministrator() && !$user->isRecruitmentMonitor()) {
                $branchScope = $user->branchScopeId();
                $app = $assessment->candidateApplication;
                if ($branchScope && $app && $app->branch_id && (int) $app->branch_id !== (int) $branchScope) {
                    throw new AuthorizationException('Access denied for candidates outside your branch.');
                }
            }
            return;
        }

        // 2. Assigned interviewer for this candidate application
        if ($user->employee) {
            $isAssigned = InterviewRound::where('candidate_application_id', $assessment->candidate_application_id)
                ->whereHas('interviewers', function ($query) use ($user) {
                    $query->where('employees.id', $user->employee->id);
                })
                ->exists();

            if ($isAssigned) {
                return;
            }
        }

        throw new AuthorizationException('Unauthorized access to candidate psychotest document.');
    }
}
