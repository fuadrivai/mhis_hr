<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * High performance composite indexes for ATS Dashboard and Reports aggregation queries.
     */
    public function up(): void
    {
        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->index(['branch_id', 'stage'], 'cand_app_branch_stage_idx');
            $table->index(['job_opening_id', 'stage'], 'cand_app_opening_stage_idx');
        });

        Schema::table('application_stage_histories', function (Blueprint $table) {
            $table->index(['to_stage', 'changed_at'], 'app_stage_hist_stage_time_idx');
        });

        Schema::table('application_onboardings', function (Blueprint $table) {
            $table->index(['join_status', 'join_date'], 'app_onboard_status_date_idx');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'offers_status_created_idx');
        });

        Schema::table('interview_evaluations', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'interview_eval_status_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interview_evaluations', function (Blueprint $table) {
            $table->dropIndex('interview_eval_status_created_idx');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->dropIndex('offers_status_created_idx');
        });

        Schema::table('application_onboardings', function (Blueprint $table) {
            $table->dropIndex('app_onboard_status_date_idx');
        });

        Schema::table('application_stage_histories', function (Blueprint $table) {
            $table->dropIndex('app_stage_hist_stage_time_idx');
        });

        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->dropIndex('cand_app_branch_stage_idx');
            $table->dropIndex('cand_app_opening_stage_idx');
        });
    }
};

