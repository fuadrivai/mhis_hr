<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_match_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_application_id');
            $table->unsignedBigInteger('job_opening_id')->nullable();
            $table->unsignedBigInteger('job_requirement_id')->nullable();
            $table->string('status', 30)->default('queued'); // queued, processing, completed, needs_manual_review, failed
            $table->unsignedTinyInteger('total_score')->nullable();
            $table->boolean('mandatory_missing')->default(false);
            $table->text('summary')->nullable();
            $table->json('red_flags')->nullable();
            $table->json('interview_questions')->nullable();
            $table->json('requirement_snapshot')->nullable();
            $table->string('requirement_hash', 64);
            $table->string('cv_hash', 64);
            $table->string('provider', 50);
            $table->string('model', 50);
            $table->string('prompt_version', 20);
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('candidate_application_id', 'cv_res_app_fk')
                ->references('id')
                ->on('candidate_applications')
                ->cascadeOnDelete();

            $table->foreign('job_opening_id', 'cv_res_opening_fk')
                ->references('id')
                ->on('job_openings')
                ->nullOnDelete();

            $table->foreign('job_requirement_id', 'cv_res_req_fk')
                ->references('id')
                ->on('job_requirements')
                ->nullOnDelete();

            $table->foreign('triggered_by', 'cv_res_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['candidate_application_id', 'status'], 'cv_res_app_status_idx');
            $table->index(['cv_hash', 'requirement_hash', 'prompt_version'], 'cv_res_cache_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_match_results');
    }
};

