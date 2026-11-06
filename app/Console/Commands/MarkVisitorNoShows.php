<?php

namespace App\Console\Commands;

use App\Models\VisitorVisit;
use App\Models\VisitorVisitLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkVisitorNoShows extends Command
{
    protected $signature = 'visitor:mark-no-shows';

    protected $description = 'Mark expected visits from previous dates as no-shows.';

    public function handle(): int
    {
        $count = 0;

        VisitorVisit::where('status', 'expected')
            ->whereDate('visit_date', '<', today())
            ->orderBy('id')
            ->chunkById(100, function ($visits) use (&$count) {
                foreach ($visits as $candidate) {
                    DB::transaction(function () use ($candidate, &$count) {
                        $visit = VisitorVisit::whereKey($candidate->id)->lockForUpdate()->first();
                        if (!$visit || $visit->status !== 'expected' || !$visit->visit_date->lt(today())) {
                            return;
                        }

                        $visit->update(['status' => 'no_show']);
                        VisitorVisitLog::create([
                            'visitor_visit_id' => $visit->id,
                            'action' => 'no_show',
                            'old_status' => 'expected',
                            'new_status' => 'no_show',
                            'description' => 'Visit date passed without check-in.',
                        ]);
                        $count++;
                    });
                }
            });

        $this->info("Marked {$count} visitor visit(s) as no-show.");

        return self::SUCCESS;
    }
}
