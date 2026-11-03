<?php

namespace App\Services;

use App\Models\CandidateApplication;
use App\Models\Employee;
use App\Models\InterviewEvaluation;
use App\Models\InterviewRound;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface RecruitmentInterviewService
{
    public function getAssignableInterviewers(): Collection;

    public function scheduleRound(
        CandidateApplication $application,
        array $data,
        array $interviewerEmployeeIds,
        User $user
    ): InterviewRound;

    public function bulkScheduleRounds(
        array $applicationIds,
        array $data,
        array $interviewerEmployeeIds,
        User $user
    ): array;

    public function updateRound(
        InterviewRound $round,
        array $data,
        ?array $interviewerEmployeeIds,
        User $user
    ): InterviewRound;

    public function cancelRound(
        InterviewRound $round,
        ?string $reason,
        User $user
    ): InterviewRound;

    public function saveEvaluationDraft(
        InterviewRound $round,
        Employee $interviewer,
        array $data,
        ?UploadedFile $attachment = null
    ): InterviewEvaluation;

    public function submitEvaluation(
        InterviewRound $round,
        Employee $interviewer,
        array $data,
        ?UploadedFile $attachment = null
    ): InterviewEvaluation;

    public function reopenEvaluation(
        InterviewEvaluation $evaluation,
        string $reason,
        User $user
    ): InterviewEvaluation;

    public function uploadManualEvaluationAttachment(
        InterviewEvaluation $evaluation,
        UploadedFile $file,
        User $user
    ): InterviewEvaluation;

    public function getInterviewerAssignedRounds(Employee $interviewer): Collection;

    public function getEvaluationSummaryForInterviewer(InterviewRound $round, Employee $interviewer): ?array;
}
