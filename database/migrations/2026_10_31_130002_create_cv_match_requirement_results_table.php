<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_match_requirement_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cv_match_result_id');
            $table->unsignedBigInteger('job_requirement_detail_id')->nullable();
            $table->text('requirement_text');
            $table->string('category', 50)->default('other');
            $table->boolean('is_mandatory')->default(false);
            $table->unsignedTinyInteger('weight')->default(3);
            $table->string('status', 20); // met, partial, not_met, unknown
            $table->text('evidence')->nullable();
            $table->boolean('evidence_verified')->default(false);
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('cv_match_result_id', 'cv_req_res_parent_fk')
                ->references('id')
                ->on('cv_match_results')
                ->cascadeOnDelete();

            $table->foreign('job_requirement_detail_id', 'cv_req_res_detail_fk')
                ->references('id')
                ->on('job_requirement_details')
                ->nullOnDelete();

            $table->index(['cv_match_result_id', 'status'], 'cv_req_res_parent_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_match_requirement_results');
    }
};

