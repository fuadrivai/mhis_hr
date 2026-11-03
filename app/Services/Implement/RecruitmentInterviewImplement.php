<?php

namespace App\Services\Implement;

use App\Mail\RecruitmentInterviewInvitationMail;
use Carbon\Carbon;
use App\Models\CandidateApplication;
use App\Models\Employee;
use App\Models\EvaluationCompetency;
use App\Models\InterviewEvaluation;
use App\Models\InterviewEvaluationScore;
use App\Models\InterviewRound;
use App\Models\InterviewRoundInterviewer;
use App\Models\User;
use App\Services\CandidateRegistrationService;
use App\Services\RecruitmentInterviewService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecruitmentInterviewImplement implements RecruitmentInterviewService
{
    protected CandidateRegistrationService $candidateService;

    public function __construct(CandidateRegistrationService $candidateService)
    {
        $this->candidateService = $candidateService;
    }

    public function getAssignableInterviewers(): Collection
    {
        return Employee::whereNotNull('user_id')
            ->where('is_active', true)
            ->with(['personal', 'user', 'employment.job_position'])
            ->get()
            ->sortBy(fn (Employee $e) => strtolower($e->personal?->first_name ?? $e->user?->name ?? ''))
            ->values();
    }

    public function bulkScheduleRounds(
        array $applicationIds,
        array $data,
        array $interviewerEmployeeIds,
        User $user
    ): array {
        if (empty($applicationIds)) {
            throw ValidationException::withMessages([
                'application_ids' => 'Please select at least one candidate application to schedule.',
            ]);
        }

        if (empty($interviewerEmployeeIds)) {
            throw ValidationException::withMessages([
                'interviewers' => 'Please select at least one interviewer for these rounds.',
            ]);
        }

        $applications = CandidateApplication::whereIn('id', $applicationIds)
            ->with(['candidate', 'branch'])
            ->get();

        if ($applications->isEmpty()) {
            throw ValidationException::withMessages([
                'application_ids' => 'No valid candidate applications found.',
            ]);
        }

        // Validate branch scoping and permissions on all applications
        foreach ($applications as $app) {
            $this->ensureCanManageInterviews($user, $app);
        }

        // Validate all interviewers exist and have user accounts
        $employees = Employee::whereIn('id', $interviewerEmployeeIds)->with(['user', 'personal'])->get();
        if ($employees->count() !== count(array_unique($interviewerEmployeeIds))) {
            throw ValidationException::withMessages([
                'interviewers' => 'One or more selected interviewers could not be found.',
            ]);
        }

        foreach ($employees as $employee) {
            if (!$employee->user_id || !$employee->user) {
                $empName = $employee->personal ? ($employee->personal->first_name . ' ' . $employee->personal->last_name) : "Employee #{$employee->id}";
                throw ValidationException::withMessages([
                    'interviewers' => "Selected interviewer '{$empName}' does not have an active system login account.",
                ]);
            }
        }

        $baseScheduledAt = $data['scheduled_at'] ?? null;
        if (!$baseScheduledAt) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'Interview schedule date and time is required.',
            ]);
        }

        $roundType = $data['round_type'] ?? 'user';
        if (!in_array($roundType, ['hr', 'user', 'panel'], true)) {
            throw ValidationException::withMessages([
                'round_type' => 'Please select a valid interview round type (HR, User, or Panel).',
            ]);
        }

        $slotInterval = isset($data['slot_interval_minutes']) ? max(0, (int) $data['slot_interval_minutes']) : 0;

        return DB::transaction(function () use ($applications, $data, $interviewerEmployeeIds, $user, $roundType, $baseScheduledAt, $slotInterval, $employees) {
            $createdRounds = [];

            foreach ($applications->values() as $index => $application) {
                $candidateScheduledAt = $slotInterval > 0
                    ? Carbon::parse($baseScheduledAt)->addMinutes($index * $slotInterval)->format('Y-m-d H:i:s')
                    : Carbon::parse($baseScheduledAt)->format('Y-m-d H:i:s');

                $maxRoundNo = (int) $application->interviewRounds()->max('round_no');
                $roundNo = $maxRoundNo + 1;

                $round = InterviewRound::create([
                    'candidate_application_id' => $application->id,
                    'round_no'                 => $roundNo,
                    'round_type'               => $roundType,
                    'scheduled_at'             => $candidateScheduledAt,
                    'location_or_link'         => $data['location_or_link'] ?? null,
                    'status'                   => 'scheduled',
                    'notes'                    => $data['notes'] ?? null,
                    'created_by'               => $user->id,
                ]);

                foreach ($interviewerEmployeeIds as $employeeId) {
                    InterviewRoundInterviewer::firstOrCreate([
                        'interview_round_id' => $round->id,
                        'employee_id'        => $employeeId,
                    ]);

                    InterviewEvaluation::firstOrCreate([
                        'interview_round_id'      => $round->id,
                        'interviewer_employee_id' => $employeeId,
                    ], [
                        'status' => 'draft',
                    ]);
                }

                // Stage transition
                $this->candidateService->updateCandidateApplication(
                    $application->candidate,
                    $application,
                    [
                        'stage'           => 'interview',
                        'stage_substatus' => 'scheduled',
                        'reason'          => "Bulk Interview Round {$roundNo} ({$round->round_type_label}) scheduled",
                    ],
                    $user
                );

                // Optional email invitation
                if (config('recruitment.mail.enabled', false)) {
                    foreach ($employees as $employee) {
                        try {
                            if ($employee->user && $employee->user->email) {
                                Mail::to($employee->user->email)->send(
                                    new RecruitmentInterviewInvitationMail($round, $employee)
                                );
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Failed sending interview invitation email: ' . $e->getMessage());
                        }
                    }
                }

                $createdRounds[] = $round;
            }

            return [
                'scheduled_count' => count($createdRounds),
                'rounds'          => $createdRounds,
            ];
        });
    }

    public function scheduleRound(
        CandidateApplication $application,
        array $data,
        array $interviewerEmployeeIds,
        User $user
    ): InterviewRound {
        $this->ensureCanManageInterviews($user, $application);

        if (empty($interviewerEmployeeIds)) {
            throw ValidationException::withMessages([
                'interviewers' => 'Please select at least one interviewer for this round.',
            ]);
        }

        // Validate that each interviewer is an employee with an active system user login account
        $employees = Employee::whereIn('id', $interviewerEmployeeIds)->with('user', 'personal')->get();
        if ($employees->count() !== count(array_unique($interviewerEmployeeIds))) {
            throw ValidationException::withMessages([
                'interviewers' => 'One or more selected interviewers could not be found.',
            ]);
        }

        foreach ($employees as $employee) {
            if (!$employee->user_id || !$employee->user) {
                $empName = $employee->personal ? ($employee->personal->first_name . ' ' . $employee->personal->last_name) : "Employee #{$employee->id}";
                throw ValidationException::withMessages([
                    'interviewers' => "Selected interviewer '{$empName}' does not have an active system login account.",
                ]);
            }
        }

        $scheduledAt = $data['scheduled_at'] ?? null;
        if (!$scheduledAt) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'Interview schedule date and time is required.',
            ]);
        }

        $roundType = $data['round_type'] ?? 'hr';
        if (!in_array($roundType, ['hr', 'user', 'panel'], true)) {
            throw ValidationException::withMessages([
                'round_type' => 'Please select a valid interview round type (HR, User, or Panel).',
            ]);
        }

        return DB::transaction(function () use ($application, $data, $interviewerEmployeeIds, $user, $roundType, $scheduledAt, $employees) {
            $maxRoundNo = (int) $application->interviewRounds()->max('round_no');
            $roundNo = !empty($data['round_no']) ? (int) $data['round_no'] : ($maxRoundNo + 1);

            $round = InterviewRound::create([
                'candidate_application_id' => $application->id,
                'round_no'                 => $roundNo,
                'round_type'               => $roundType,
                'scheduled_at'             => $scheduledAt,
                'location_or_link'         => $data['location_or_link'] ?? null,
                'status'                   => 'scheduled',
                'notes'                    => $data['notes'] ?? null,
                'created_by'               => $user->id,
            ]);

            // Link interviewers and create initial draft evaluations
            foreach ($interviewerEmployeeIds as $employeeId) {
                InterviewRoundInterviewer::firstOrCreate([
                    'interview_round_id' => $round->id,
                    'employee_id'        => $employeeId,
                ]);

                InterviewEvaluation::firstOrCreate([
                    'interview_round_id'      => $round->id,
                    'interviewer_employee_id' => $employeeId,
                ], [
                    'status' => 'draft',
                ]);
            }

            // Transition candidate stage to 'interview' with substatus 'scheduled'
            $this->candidateService->updateCandidateApplication(
                $application->candidate,
                $application,
                [
                    'stage'           => 'interview',
                    'stage_substatus' => 'scheduled',
                    'reason'          => "Interview Round {$roundNo} scheduled",
                ],
                $user
            );

            // Optional email notification if configured
            if (config('recruitment.mail.enabled', false)) {
                foreach ($employees as $employee) {
                    try {
                        if ($employee->user && $employee->user->email) {
                            Mail::to($employee->user->email)->send(
                                new RecruitmentInterviewInvitationMail($round, $employee)
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Failed sending interview invitation email: ' . $e->getMessage());
                    }
                }
            }

            return $round->load('interviewers.personal', 'evaluations');
        });
    }

    public function updateRound(
        InterviewRound $round,
        array $data,
        ?array $interviewerEmployeeIds,
        User $user
    ): InterviewRound {
        $this->ensureCanManageInterviews($user, $round->candidateApplication);

        return DB::transaction(function () use ($round, $data, $interviewerEmployeeIds) {
            $round->update([
                'round_type'       => $data['round_type'] ?? $round->round_type,
                'scheduled_at'     => $data['scheduled_at'] ?? $round->scheduled_at,
                'location_or_link' => $data['location_or_link'] ?? $round->location_or_link,
                'notes'            => $data['notes'] ?? $round->notes,
            ]);

            if ($interviewerEmployeeIds !== null && !empty($interviewerEmployeeIds)) {
                $employees = Employee::whereIn('id', $interviewerEmployeeIds)->with('user')->get();
                foreach ($employees as $employee) {
                    if (!$employee->user_id || !$employee->user) {
                        $empName = $employee->personal ? ($employee->personal->first_name . ' ' . $employee->personal->last_name) : "Employee #{$employee->id}";
                        throw ValidationException::withMessages([
                            'interviewers' => "Selected interviewer '{$empName}' does not have an active system login account.",
                        ]);
                    }
                }

                $round->interviewers()->sync($interviewerEmployeeIds);

                foreach ($interviewerEmployeeIds as $employeeId) {
                    InterviewEvaluation::firstOrCreate([
                        'interview_round_id'      => $round->id,
                        'interviewer_employee_id' => $employeeId,
                    ], [
                        'status' => 'draft',
                    ]);
                }
            }

            return $round->fresh(['interviewers.personal', 'evaluations']);
        });
    }

    public function cancelRound(
        InterviewRound $round,
        ?string $reason,
        User $user
    ): InterviewRound {
        $this->ensureCanManageInterviews($user, $round->candidateApplication);

        return DB::transaction(function () use ($round, $reason, $user) {
            $round->update([
                'status' => 'cancelled',
                'notes'  => $round->notes . ($reason ? ("\nCancelled: " . $reason) : ''),
            ]);

            // If all rounds are cancelled or none active, reflect in substatus
            $hasActiveRounds = $round->candidateApplication->interviewRounds()
                ->where('id', '!=', $round->id)
                ->where('status', 'scheduled')
                ->exists();

            if (!$hasActiveRounds && $round->candidateApplication->stage === 'interview') {
                $this->candidateService->updateCandidateApplication(
                    $round->candidateApplication->candidate,
                    $round->candidateApplication,
                    [
                        'stage'           => 'interview',
                        'stage_substatus' => 'cancelled',
                        'reason'          => 'Interview round cancelled: ' . ($reason ?? 'No reason given'),
                    ],
                    $user
                );
            }

            return $round;
        });
    }

    public function saveEvaluationDraft(
        InterviewRound $round,
        Employee $interviewer,
        array $data,
        ?UploadedFile $attachment = null
    ): InterviewEvaluation {
        $evaluation = $this->getOrCreateEvaluation($round, $interviewer);

        if ($evaluation->status === 'submitted') {
            throw ValidationException::withMessages([
                'evaluation' => 'This evaluation has already been submitted and cannot be modified as a draft.',
            ]);
        }

        return DB::transaction(function () use ($round, $evaluation, $data, $attachment) {
            $updateData = [
                'overall_recommendation' => $data['overall_recommendation'] ?? $evaluation->overall_recommendation,
                'strengths'              => $data['strengths'] ?? $evaluation->strengths,
                'concerns'               => $data['concerns'] ?? $evaluation->concerns,
                'notes'                  => $data['notes'] ?? $evaluation->notes,
                'status'                 => 'draft',
            ];

            if ($attachment) {
                $updateData['attachment_path'] = $attachment->storeAs(
                    'recruitment/evaluations',
                    Str::uuid()->toString() . '.' . $attachment->getClientOriginalExtension(),
                    'local'
                );
                $updateData['original_attachment_name'] = $attachment->getClientOriginalName();
            }

            $evaluation->update($updateData);

            if (!empty($data['scores']) && is_array($data['scores'])) {
                foreach ($data['scores'] as $competencyId => $scoreValue) {
                    if ($scoreValue !== null && $scoreValue !== '') {
                        $comment = $data['comments'][$competencyId] ?? null;
                        InterviewEvaluationScore::updateOrCreate(
                            [
                                'interview_evaluation_id' => $evaluation->id,
                                'competency_id'           => $competencyId,
                            ],
                            [
                                'score'   => (int) $scoreValue,
                                'comment' => $comment,
                            ]
                        );
                    }
                }
            }

            // Update candidate substatus to 'awaiting_evaluation'
            $app = $round->candidateApplication;
            if ($app->stage === 'interview' && $app->stage_substatus !== 'awaiting_evaluation' && $app->stage_substatus !== 'done') {
                $app->update(['stage_substatus' => 'awaiting_evaluation']);
            }

            return $evaluation->fresh('scores.competency');
        });
    }

    public function submitEvaluation(
        InterviewRound $round,
        Employee $interviewer,
        array $data,
        ?UploadedFile $attachment = null
    ): InterviewEvaluation {
        $evaluation = $this->getOrCreateEvaluation($round, $interviewer);

        if ($evaluation->status === 'submitted') {
            throw ValidationException::withMessages([
                'evaluation' => 'This evaluation has already been submitted.',
            ]);
        }

        $rec = $data['overall_recommendation'] ?? null;
        if (!in_array($rec, ['strongly_recommend', 'recommend', 'hold', 'not_recommend'], true)) {
            throw ValidationException::withMessages([
                'overall_recommendation' => 'Please select an overall recommendation (Strongly Recommend, Recommend, Hold, or Not Recommend).',
            ]);
        }

        // Validate that all active competencies have a valid score between 1 and 5
        $activeCompetencies = EvaluationCompetency::active()->get();
        $submittedScores = $data['scores'] ?? [];
        foreach ($activeCompetencies as $comp) {
            $score = $submittedScores[$comp->id] ?? null;
            if ($score === null || $score === '' || (int) $score < 1 || (int) $score > 5) {
                throw ValidationException::withMessages([
                    "scores.{$comp->id}" => "Please provide a valid score (1 to 5) for {$comp->name}.",
                ]);
            }
        }

        return DB::transaction(function () use ($round, $evaluation, $data, $attachment, $rec, $activeCompetencies, $submittedScores) {
            $updateData = [
                'overall_recommendation' => $rec,
                'strengths'              => $data['strengths'] ?? null,
                'concerns'               => $data['concerns'] ?? null,
                'notes'                  => $data['notes'] ?? null,
                'status'                 => 'submitted',
                'submitted_at'           => now(),
            ];

            if ($attachment) {
                $updateData['attachment_path'] = $attachment->storeAs(
                    'recruitment/evaluations',
                    Str::uuid()->toString() . '.' . $attachment->getClientOriginalExtension(),
                    'local'
                );
                $updateData['original_attachment_name'] = $attachment->getClientOriginalName();
            }

            $evaluation->update($updateData);

            foreach ($activeCompetencies as $comp) {
                $score = (int) $submittedScores[$comp->id];
                $comment = $data['comments'][$comp->id] ?? null;
                InterviewEvaluationScore::updateOrCreate(
                    [
                        'interview_evaluation_id' => $evaluation->id,
                        'competency_id'           => $comp->id,
                    ],
                    [
                        'score'   => $score,
                        'comment' => $comment,
                    ]
                );
            }

            // Check if all interviewers on this round have submitted
            $totalInterviewers = $round->interviewers()->count();
            $submittedCount = $round->evaluations()->where('status', 'submitted')->count();

            $app = $round->candidateApplication;
            if ($submittedCount >= $totalInterviewers && $totalInterviewers > 0) {
                $round->update(['status' => 'done']);

                // If all rounds on the application are done, set substatus to done
                $pendingRounds = $app->interviewRounds()
                    ->where('status', 'scheduled')
                    ->exists();

                if (!$pendingRounds && $app->stage === 'interview') {
                    $app->update(['stage_substatus' => 'done']);
                }
            } else {
                if ($app->stage === 'interview') {
                    $app->update(['stage_substatus' => 'awaiting_evaluation']);
                }
            }

            return $evaluation->fresh('scores.competency');
        });
    }

    public function reopenEvaluation(
        InterviewEvaluation $evaluation,
        string $reason,
        User $user
    ): InterviewEvaluation {
        if (!$user->isRecruitmentAdministrator() && !$user->isRecruitmentMonitor()) {
            throw new AuthorizationException('Only Recruitment Administrators or Monitors can reopen an evaluation.');
        }

        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'A mandatory reason is required to reopen a submitted interview evaluation.',
            ]);
        }

        return DB::transaction(function () use ($evaluation, $reason, $user) {
            $round = $evaluation->interviewRound;

            $evaluation->update([
                'status'          => 'draft',
                'reopened_reason' => $reason,
                'reopened_by'     => $user->id,
            ]);

            // Revert round status to scheduled if it was done
            if ($round->status === 'done') {
                $round->update(['status' => 'scheduled']);
            }

            $app = $round->candidateApplication;
            if ($app->stage === 'interview') {
                $app->update(['stage_substatus' => 'awaiting_evaluation']);
            }

            return $evaluation;
        });
    }

    public function uploadManualEvaluationAttachment(
        InterviewEvaluation $evaluation,
        UploadedFile $file,
        User $user
    ): InterviewEvaluation {
        $this->ensureCanManageInterviews($user, $evaluation->interviewRound->candidateApplication);

        $storedPath = $file->storeAs(
            'recruitment/evaluations',
            Str::uuid()->toString() . '.' . $file->getClientOriginalExtension(),
            'local'
        );

        $evaluation->update([
            'attachment_path'          => $storedPath,
            'original_attachment_name' => $file->getClientOriginalName(),
        ]);

        return $evaluation;
    }

    public function getInterviewerAssignedRounds(Employee $interviewer): Collection
    {
        return InterviewRound::whereHas('interviewers', function ($query) use ($interviewer) {
            $query->where('employees.id', $interviewer->id);
        })
            ->with([
                'candidateApplication.candidate',
                'candidateApplication.jobOpening',
                'candidateApplication.assessments',
                'evaluations' => function ($query) use ($interviewer) {
                    $query->where('interviewer_employee_id', $interviewer->id);
                },
            ])
            ->orderBy('scheduled_at', 'desc')
            ->get();
    }

    public function getEvaluationSummaryForInterviewer(InterviewRound $round, Employee $interviewer): ?array
    {
        // Check if this interviewer is assigned to the round
        $isAssigned = $round->interviewers()->where('employees.id', $interviewer->id)->exists();
        if (!$isAssigned) {
            throw new AuthorizationException('You are not assigned to this interview round.');
        }

        // Blind review rule: interviewer cannot see peer evaluations before submitting their own!
        $myEvaluation = $round->evaluations()->where('interviewer_employee_id', $interviewer->id)->first();
        if (!$myEvaluation || $myEvaluation->status !== 'submitted') {
            return null; // Blind review enforced
        }

        // Once submitted, return summary of other submitted evaluations on this round
        $peerEvaluations = $round->evaluations()
            ->where('interviewer_employee_id', '!=', $interviewer->id)
            ->where('status', 'submitted')
            ->with('interviewerEmployee.personal', 'scores.competency')
            ->get();

        return [
            'my_evaluation'    => $myEvaluation->load('scores.competency'),
            'peer_evaluations' => $peerEvaluations,
        ];
    }

    protected function getOrCreateEvaluation(InterviewRound $round, Employee $interviewer): InterviewEvaluation
    {
        $isAssigned = $round->interviewers()->where('employees.id', $interviewer->id)->exists();
        if (!$isAssigned) {
            throw new AuthorizationException('You are not assigned as an interviewer for this round.');
        }

        return InterviewEvaluation::firstOrCreate([
            'interview_round_id'      => $round->id,
            'interviewer_employee_id' => $interviewer->id,
        ], [
            'status' => 'draft',
        ]);
    }

    protected function ensureCanManageInterviews(User $user, CandidateApplication $application): void
    {
        if (!$user->canAccessRecruitment()) {
            throw new AuthorizationException('You are not authorized to manage candidate interviews.');
        }

        if (!$user->isRecruitmentAdministrator() && !$user->isRecruitmentMonitor()) {
            $branchScope = $user->branchScopeId();
            if ($branchScope && $application->branch_id && (int) $application->branch_id !== (int) $branchScope) {
                throw new AuthorizationException('Access denied for candidates outside your branch.');
            }
        }
    }
}

