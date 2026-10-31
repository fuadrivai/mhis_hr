<?php

namespace App\Jobs;

use App\Models\CandidateApplication;
use App\Models\User;
use App\Services\Recruitment\Ai\CvMatchingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessCvMatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 120;

    public function __construct(
        public int $candidateApplicationId,
        public ?int $triggeredById = null,
        public bool $forceRerun = false
    ) {
    }

    public function handle(CvMatchingService $matchingService): void
    {
        $application = CandidateApplication::find($this->candidateApplicationId);
        if (!$application) {
            Log::warning("ProcessCvMatchJob: CandidateApplication #{$this->candidateApplicationId} not found.");
            return;
        }

        $user = $this->triggeredById ? User::find($this->triggeredById) : null;

        try {
            $matchingService->matchApplication($application, $user, $this->forceRerun);
        } catch (Throwable $e) {
            Log::error("ProcessCvMatchJob failed for application #{$application->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}

