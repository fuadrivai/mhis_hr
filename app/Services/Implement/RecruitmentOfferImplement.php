<?php

namespace App\Services\Implement;

use App\Models\ApplicationOnboarding;
use App\Models\ApplicationStageHistory;
use App\Models\CandidateApplication;
use App\Models\Employee;
use App\Models\Offer;
use App\Models\OfferApprovalStep;
use App\Models\User;
use App\Services\RecruitmentOfferService;
use App\Services\Recruitment\StageTransitionGuard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecruitmentOfferImplement implements RecruitmentOfferService
{
    protected StageTransitionGuard $transitionGuard;

    public function __construct(StageTransitionGuard $transitionGuard)
    {
        $this->transitionGuard = $transitionGuard;
    }

    protected function authorizeRecruitment(User $user): void
    {
        if (!$user->canAccessRecruitment()) {
            throw new AuthorizationException('You are not authorized to manage recruitment offers.');
        }
    }

    public function createOffer(CandidateApplication $application, array $data, User $user): Offer
    {
        $this->authorizeRecruitment($user);

        return DB::transaction(function () use ($application, $data, $user) {
            $this->validateApprovers($data['approvers'] ?? [], $user);

            // Cancel any previous pending or draft offers on this application
            Offer::where('candidate_application_id', $application->id)
                ->whereIn('status', ['draft', 'pending_approval'])
                ->update(['status' => 'cancelled']);

            $latestVersion = (int) Offer::where('candidate_application_id', $application->id)->max('version');
            $newVersion = $latestVersion + 1;

            $status = !empty($data['submit_for_approval']) ? 'pending_approval' : 'draft';

            $offer = Offer::create([
                'candidate_application_id' => $application->id,
                'job_opening_id'           => $application->job_opening_id,
                'position_title'           => trim($data['position_title']),
                'grade_level'              => !empty($data['grade_level']) ? trim($data['grade_level']) : null,
                'basic_salary'             => (string) $data['basic_salary'],
                'allowances'               => $data['allowances'] ?? [],
                'other_benefits'           => !empty($data['other_benefits']) ? trim($data['other_benefits']) : null,
                'proposed_start_date'      => $data['proposed_start_date'] ?? null,
                'valid_until'              => $data['valid_until'] ?? null,
                'offer_letter_url'         => !empty($data['offer_letter_url']) ? trim($data['offer_letter_url']) : null,
                'status'                   => $status,
                'version'                  => $newVersion,
                'notes'                    => !empty($data['notes']) ? trim($data['notes']) : null,
                'created_by'               => $user->id,
            ]);

            $this->createApprovalSteps($offer, $data['approvers'] ?? []);

            // Progress application stage to offer if not already there
            if (in_array($application->stage, ['applied', 'screening', 'psychotest', 'interview'], true)) {
                $this->transitionGuard->validateTransition($application, 'offer', 'drafting', 'Offer created', $user);

                $fromStage = $application->stage;
                $application->update([
                    'stage'            => 'offer',
                    'status'           => StageTransitionGuard::mapAggregateStatus('offer'),
                    'stage_substatus'  => $status === 'pending_approval' ? 'awaiting_approval' : 'draft',
                    'last_activity_at' => now(),
                ]);

                ApplicationStageHistory::create([
                    'candidate_application_id' => $application->id,
                    'from_stage'               => $fromStage,
                    'to_stage'                 => 'offer',
                    'from_substatus'           => $application->stage_substatus,
                    'to_substatus'             => $status === 'pending_approval' ? 'awaiting_approval' : 'draft',
                    'changed_by'               => $user->id,
                    'reason'                   => "Offer version {$newVersion} created",
                    'changed_at'               => now(),
                ]);
            }

            return $offer->load(['approvalSteps.approver.personal', 'creator', 'candidateApplication']);
        });
    }

    public function updateOffer(Offer $offer, array $data, User $user): Offer
    {
        $this->authorizeRecruitment($user);

        if (!in_array($offer->status, ['draft', 'pending_approval'], true)) {
            throw ValidationException::withMessages([
                'offer' => 'Only offers in draft or pending approval status can be edited.',
            ]);
        }

        return DB::transaction(function () use ($offer, $data, $user) {
            $this->validateApprovers($data['approvers'] ?? [], $user);

            $status = !empty($data['submit_for_approval']) ? 'pending_approval' : 'draft';

            $offer->update([
                'position_title'      => trim($data['position_title']),
                'grade_level'         => !empty($data['grade_level']) ? trim($data['grade_level']) : null,
                'basic_salary'        => (string) $data['basic_salary'],
                'allowances'          => $data['allowances'] ?? [],
                'other_benefits'      => !empty($data['other_benefits']) ? trim($data['other_benefits']) : null,
                'proposed_start_date' => $data['proposed_start_date'] ?? null,
                'valid_until'         => $data['valid_until'] ?? null,
                'offer_letter_url'    => !empty($data['offer_letter_url']) ? trim($data['offer_letter_url']) : null,
                'status'              => $status,
                'notes'               => !empty($data['notes']) ? trim($data['notes']) : null,
            ]);

            // Re-sync approval steps
            $offer->approvalSteps()->delete();
            $this->createApprovalSteps($offer, $data['approvers'] ?? []);

            $substatus = $status === 'pending_approval' ? 'awaiting_approval' : 'draft';
            $offer->candidateApplication->update([
                'stage_substatus'  => $substatus,
                'last_activity_at' => now(),
            ]);

            return $offer->load(['approvalSteps.approver.personal', 'creator', 'candidateApplication']);
        });
    }

    public function submitForApproval(Offer $offer, User $user): Offer
    {
        $this->authorizeRecruitment($user);

        if ($offer->status !== 'draft') {
            throw ValidationException::withMessages([
                'offer' => 'Only draft offers can be submitted for approval.',
            ]);
        }

        if ($offer->approvalSteps()->count() === 0) {
            throw ValidationException::withMessages([
                'approvers' => 'At least one approver is required before submitting for approval.',
            ]);
        }

        $offer->update(['status' => 'pending_approval']);
        $offer->candidateApplication->update([
            'stage_substatus'  => 'awaiting_approval',
            'last_activity_at' => now(),
        ]);

        return $offer->load('approvalSteps.approver.personal');
    }

    public function approveStep(Offer $offer, int $employeeId, ?string $comment, User $user): Offer
    {
        if ($offer->status !== 'pending_approval') {
            throw ValidationException::withMessages([
                'offer' => 'This offer is not currently awaiting approval.',
            ]);
        }

        $activeLevel = $offer->currentActiveLevel();
        if ($activeLevel === null) {
            throw ValidationException::withMessages([
                'offer' => 'There are no active approval levels pending for this offer.',
            ]);
        }

        // Prevent self-approval if creator is the approver
        if ($offer->created_by === $user->id) {
            throw new AuthorizationException('You cannot approve an offer that you created.');
        }

        // Must match user's own employee id unless Administrator override
        if ($user->employee?->id !== $employeeId && !$user->isRecruitmentAdministrator()) {
            throw new AuthorizationException('You are not authorized to act on behalf of this approver.');
        }

        $step = $offer->approvalSteps()
            ->where('approver_employee_id', $employeeId)
            ->where('level', $activeLevel)
            ->where('status', 'pending')
            ->first();

        if (!$step) {
            throw ValidationException::withMessages([
                'step' => 'No pending approval step found for you at the current approval level.',
            ]);
        }

        return DB::transaction(function () use ($offer, $step, $comment) {
            $step->update([
                'status'   => 'approved',
                'acted_at' => now(),
                'comment'  => !empty($comment) ? trim($comment) : null,
            ]);

            // Check if all steps are now approved
            $offer->refresh();
            if ($offer->areAllStepsApproved()) {
                $offer->update(['status' => 'approved']);
                $offer->candidateApplication->update([
                    'stage_substatus'  => 'approved',
                    'last_activity_at' => now(),
                ]);
            }

            return $offer->load('approvalSteps.approver.personal');
        });
    }

    public function rejectStep(Offer $offer, int $employeeId, string $comment, User $user): Offer
    {
        if (empty(trim($comment))) {
            throw ValidationException::withMessages([
                'comment' => 'A mandatory comment is required when rejecting an offer.',
            ]);
        }

        if ($offer->status !== 'pending_approval') {
            throw ValidationException::withMessages([
                'offer' => 'This offer is not currently awaiting approval.',
            ]);
        }

        $activeLevel = $offer->currentActiveLevel();
        if ($activeLevel === null) {
            throw ValidationException::withMessages([
                'offer' => 'There are no active approval levels pending for this offer.',
            ]);
        }

        if ($offer->created_by === $user->id) {
            throw new AuthorizationException('You cannot act on an offer that you created.');
        }

        if ($user->employee?->id !== $employeeId && !$user->isRecruitmentAdministrator()) {
            throw new AuthorizationException('You are not authorized to act on behalf of this approver.');
        }

        $step = $offer->approvalSteps()
            ->where('approver_employee_id', $employeeId)
            ->where('level', $activeLevel)
            ->where('status', 'pending')
            ->first();

        if (!$step) {
            throw ValidationException::withMessages([
                'step' => 'No pending approval step found for you at the current approval level.',
            ]);
        }

        return DB::transaction(function () use ($offer, $step, $comment, $user) {
            $step->update([
                'status'   => 'rejected',
                'acted_at' => now(),
                'comment'  => trim($comment),
            ]);

            // Revert offer to draft so HR can revise and bump version upon resubmission
            $offer->update(['status' => 'draft']);

            $offer->candidateApplication->update([
                'stage_substatus'  => 'rejected_by_approver',
                'last_activity_at' => now(),
            ]);

            ApplicationStageHistory::create([
                'candidate_application_id' => $offer->candidate_application_id,
                'from_stage'               => 'offer',
                'to_stage'                 => 'offer',
                'from_substatus'           => 'awaiting_approval',
                'to_substatus'             => 'rejected_by_approver',
                'changed_by'               => $user->id,
                'reason'                   => "Offer rejected by approver: {$comment}",
                'changed_at'               => now(),
            ]);

            return $offer->load('approvalSteps.approver.personal');
        });
    }

    public function markAsSent(Offer $offer, array $data, User $user): Offer
    {
        $this->authorizeRecruitment($user);

        if (!in_array($offer->status, ['approved', 'sent'], true)) {
            throw ValidationException::withMessages([
                'offer' => 'Only fully approved offers can be marked as sent to the candidate.',
            ]);
        }

        return DB::transaction(function () use ($offer, $data, $user) {
            $sentAt = !empty($data['sent_at']) ? $data['sent_at'] : now();
            $url = !empty($data['offer_letter_url']) ? trim($data['offer_letter_url']) : $offer->offer_letter_url;

            $offer->update([
                'status'           => 'sent',
                'sent_at'          => $sentAt,
                'offer_letter_url' => $url,
            ]);

            $offer->candidateApplication->update([
                'stage_substatus'  => 'sent',
                'last_activity_at' => now(),
            ]);

            ApplicationStageHistory::create([
                'candidate_application_id' => $offer->candidate_application_id,
                'from_stage'               => 'offer',
                'to_stage'                 => 'offer',
                'from_substatus'           => 'approved',
                'to_substatus'             => 'sent',
                'changed_by'               => $user->id,
                'reason'                   => 'Offer sent to candidate with document reference',
                'changed_at'               => now(),
            ]);

            return $offer;
        });
    }

    public function recordCandidateResponse(Offer $offer, string $status, ?string $declineReason, User $user): Offer
    {
        $this->authorizeRecruitment($user);

        if (!in_array($status, ['accepted', 'declined', 'negotiating'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Invalid candidate response status.',
            ]);
        }

        if ($status === 'declined' && empty(trim((string) $declineReason))) {
            throw ValidationException::withMessages([
                'decline_reason' => 'A mandatory reason is required when a candidate declines an offer.',
            ]);
        }

        return DB::transaction(function () use ($offer, $status, $declineReason, $user) {
            $offer->update([
                'status'         => $status,
                'responded_at'   => now(),
                'decline_reason' => $status === 'declined' ? trim($declineReason) : null,
            ]);

            $application = $offer->candidateApplication;
            $application->update([
                'stage_substatus'  => $status,
                'last_activity_at' => now(),
            ]);

            // When accepted, initialize onboarding record if not existing
            if ($status === 'accepted') {
                ApplicationOnboarding::firstOrCreate(
                    ['candidate_application_id' => $application->id],
                    [
                        'offer_id'        => $offer->id,
                        'contract_status' => 'pending',
                        'join_status'     => 'pending',
                        'join_date'       => $offer->proposed_start_date,
                    ]
                );
            }

            ApplicationStageHistory::create([
                'candidate_application_id' => $application->id,
                'from_stage'               => 'offer',
                'to_stage'                 => 'offer',
                'from_substatus'           => 'sent',
                'to_substatus'             => $status,
                'changed_by'               => $user->id,
                'reason'                   => "Candidate response recorded: {$status}" . ($declineReason ? " ({$declineReason})" : ''),
                'changed_at'               => now(),
            ]);

            return $offer;
        });
    }

    public function cancelOffer(Offer $offer, ?string $reason, User $user): Offer
    {
        $this->authorizeRecruitment($user);

        $offer->update(['status' => 'cancelled']);
        $offer->candidateApplication->update([
            'stage_substatus'  => 'cancelled',
            'last_activity_at' => now(),
        ]);

        return $offer;
    }

    public function getMyPendingApprovals(User $user): Collection
    {
        $employee = $user->employee;
        if (!$employee) {
            return collect();
        }

        $pendingSteps = OfferApprovalStep::with(['offer.candidateApplication.candidate', 'offer.creator'])
            ->where('approver_employee_id', $employee->id)
            ->where('status', 'pending')
            ->whereHas('offer', function ($q) {
                $q->where('status', 'pending_approval');
            })
            ->get();

        // Filter only steps that are at the offer's currentActiveLevel
        return $pendingSteps->filter(function (OfferApprovalStep $step) {
            return $step->offer->currentActiveLevel() === $step->level;
        });
    }

    protected function validateApprovers(array $approvers, User $user): void
    {
        if (empty($approvers)) {
            return;
        }

        $employeeIds = [];
        $creatorEmployeeId = $user->employee?->id;

        foreach ($approvers as $idx => $step) {
            $employeeId = (int) ($step['employee_id'] ?? 0);
            $level = (int) ($step['level'] ?? 1);

            if ($employeeId <= 0) {
                throw ValidationException::withMessages([
                    "approvers.{$idx}.employee_id" => 'Valid employee is required for each approval step.',
                ]);
            }

            if ($level <= 0) {
                throw ValidationException::withMessages([
                    "approvers.{$idx}.level" => 'Approval level must be 1 or higher.',
                ]);
            }

            // Cannot self-approve
            if ($creatorEmployeeId && $employeeId === $creatorEmployeeId) {
                throw ValidationException::withMessages([
                    "approvers.{$idx}.employee_id" => 'You cannot assign yourself as an approver on an offer you are creating.',
                ]);
            }

            // Cannot duplicate approver on same offer
            if (in_array($employeeId, $employeeIds, true)) {
                throw ValidationException::withMessages([
                    "approvers.{$idx}.employee_id" => 'An employee cannot be added multiple times as an approver on the same offer.',
                ]);
            }

            $employeeIds[] = $employeeId;
        }
    }

    protected function createApprovalSteps(Offer $offer, array $approvers): void
    {
        // Sort approvers by level ascending
        usort($approvers, fn ($a, $b) => ($a['level'] ?? 1) <=> ($b['level'] ?? 1));

        foreach ($approvers as $step) {
            OfferApprovalStep::create([
                'offer_id'             => $offer->id,
                'level'                => (int) ($step['level'] ?? 1),
                'approver_employee_id' => (int) $step['employee_id'],
                'status'               => 'pending',
            ]);
        }
    }
}

