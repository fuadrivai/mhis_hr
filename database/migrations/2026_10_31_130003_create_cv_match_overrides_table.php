<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_match_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cv_match_result_id');
            $table->unsignedTinyInteger('override_score')->nullable();
            $table->text('note');
            $table->unsignedBigInteger('overridden_by');
            $table->timestamps();

            $table->foreign('cv_match_result_id', 'cv_ovr_res_fk')
                ->references('id')
                ->on('cv_match_results')
                ->cascadeOnDelete();

            $table->foreign('overridden_by', 'cv_ovr_user_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->index(['cv_match_result_id', 'created_at'], 'cv_ovr_res_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_match_overrides');
    }
};

