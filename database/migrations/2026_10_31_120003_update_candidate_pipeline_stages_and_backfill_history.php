<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const STAGE_MAP_UP = [
        'received'        => 'applied',
        'screening'       => 'screening',
        'shortlisted'     => 'screening',
        'assessment'      => 'psychotest',
        'interview'       => 'interview',
        'reference_check' => 'interview',
        'offer'           => 'offer',
        'rejected'        => 'rejected',
        'withdrawn'       => 'withdrawn',
        'hired'           => 'joined',
    ];

    private const STATUS_MAP_UP = [
        'applied'          => 'received',
        'screening'        => 'in_progress',
        'psychotest'       => 'in_progress',
        'interview'        => 'in_progress',
        'offer'            => 'in_progress',
        'contract_signing' => 'in_progress',
        'joined'           => 'hired',
        'rejected'         => 'rejected',
        'withdrawn'        => 'withdrawn',
    ];

    public function up(): void
    {
        $applications = DB::table('candidate_applications')->get();

        foreach ($applications as $app) {
            $newStage = self::STAGE_MAP_UP[$app->stage] ?? $app->stage;
            $newStatus = self::STATUS_MAP_UP[$newStage] ?? $app->status;

            DB::table('candidate_applications')
                ->where('id', $app->id)
                ->update([
                    'stage'  => $newStage,
                    'status' => $newStatus,
                ]);

            $hasHistory = DB::table('application_stage_histories')
                ->where('candidate_application_id', $app->id)
                ->exists();

            if (!$hasHistory) {
                DB::table('application_stage_histories')->insert([
                    'candidate_application_id' => $app->id,
                    'from_stage'               => null,
                    'to_stage'                 => $newStage,
                    'from_substatus'           => null,
                    'to_substatus'             => null,
                    'changed_by'               => null,
                    'reason'                   => 'Initial backfill',
                    'changed_at'               => $app->applied_at ?? now(),
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $reverseMap = [
            'applied'          => 'received',
            'psychotest'       => 'assessment',
            'contract_signing' => 'hired',
            'joined'           => 'hired',
        ];

        foreach ($reverseMap as $newStage => $oldStage) {
            DB::table('candidate_applications')
                ->where('stage', $newStage)
                ->update(['stage' => $oldStage]);
        }

        DB::table('application_stage_histories')
            ->where('reason', 'Initial backfill')
            ->delete();
    }
};

