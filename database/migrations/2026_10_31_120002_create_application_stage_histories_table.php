<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_application_id');
            $table->string('from_stage', 50)->nullable();
            $table->string('to_stage', 50);
            $table->string('from_substatus', 50)->nullable();
            $table->string('to_substatus', 50)->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->foreign('candidate_application_id', 'app_stage_hist_cand_app_fk')
                ->references('id')
                ->on('candidate_applications')
                ->cascadeOnDelete();

            $table->foreign('changed_by', 'app_stage_hist_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['candidate_application_id', 'changed_at'], 'app_stage_hist_app_changed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_stage_histories');
    }
};
