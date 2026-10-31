<?php

namespace App\Services\Recruitment;

use App\Models\CandidateApplication;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class StageTransitionGuard
{
    public const STAGES = [
        'applied'          => 'Applied',
        'screening'        => 'Screening',
        'psychotest'       => 'Psychotest',
        'interview'        => 'Interview',
        'offer'            => 'Offer',
        'contract_signing' => 'Contract Signing',
        'joined'           => 'Joined',
        'rejected'         => 'Rejected',
        'withdrawn'        => 'Withdrawn',
    ];

    public const FORWARD_PIPELINE = [
        'applied',
        'screening',
        'psychotest',
        'interview',
        'offer',
        'contract_signing',
        'joined',
    ];

    public const TERMINAL_STAGES = [
        'joined',
    ];

    /**
     * Map stage to aggregate status.
     */
    public static function mapAggregateStatus(string $stage): string
    {
        return match ($stage) {
            'applied' => 'received',
            'screening', 'psychotest', 'interview', 'offer', 'contract_signing' => 'in_progress',
            'joined' => 'hired',
            'rejected' => 'rejected',
            'withdrawn' => 'withdrawn',
            default => 'in_progress',
        };
    }

    /**
     * Validate transition from current stage to target stage.
     * Throws ValidationException if not allowed.
     */
    public function validateTransition(
        CandidateApplication $application,
        string $targetStage,
        ?string $substatus,
        ?string $reason,
        User $user
    ): void {
        if (!array_key_exists($targetStage, self::STAGES)) {
            throw ValidationException::withMessages([
                'stage' => "Stage '{$targetStage}' is not supported.",
            ]);
        }

        $currentStage = $application->stage;

        // Same stage transition (e.g. substatus or HR PIC update only)
        if ($currentStage === $targetStage) {
            return;
        }

        // Cannot move out of terminal joined stage unless specifically reverted with reason
        if ($currentStage === 'joined') {
            if (empty(trim((string) $reason))) {
                throw ValidationException::withMessages([
                    'reason' => 'A reason is required to revert a candidate who has already joined.',
                ]);
            }
            return;
        }

        // Transitioning to rejected or withdrawn requires a reason
        if (in_array($targetStage, ['rejected', 'withdrawn'], true)) {
            if (empty(trim((string) $reason))) {
                throw ValidationException::withMessages([
                    'reason' => "A reason is mandatory when marking an application as {$targetStage}.",
                ]);
            }
            return;
        }

        // Reopening from rejected or withdrawn
        if (in_array($currentStage, ['rejected', 'withdrawn'], true)) {
            if (empty(trim((string) $reason))) {
                throw ValidationException::withMessages([
                    'reason' => "A reason is required to reopen an application from {$currentStage}.",
                ]);
            }
            return;
        }

        // Check forward vs backward in the main pipeline
        $currentIndex = array_search($currentStage, self::FORWARD_PIPELINE, true);
        $targetIndex = array_search($targetStage, self::FORWARD_PIPELINE, true);

        if ($currentIndex !== false && $targetIndex !== false) {
            // Backward step: requires reason
            if ($targetIndex < $currentIndex) {
                if (empty(trim((string) $reason))) {
                    throw ValidationException::withMessages([
                        'reason' => 'A reason is mandatory when moving a candidate back to a previous stage.',
                    ]);
                }
            }

            // Normal forward step: gate checks for future phases
            $this->checkForwardGates($application, $targetStage, $user);
        }
    }

    /**
     * Placeholder hooks for Phase 3 and Phase 4 gates.
     */
    protected function checkForwardGates(CandidateApplication $application, string $targetStage, User $user): void
    {
        // Phase 3 gate: offer requires completed interview evaluations (to be expanded in Phase 3)
        // Phase 4 gate: contract_signing requires accepted offer; joined requires signed contract (to be expanded in Phase 4)
    }
}

