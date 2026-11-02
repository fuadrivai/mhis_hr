<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_evaluation_id')->constrained('interview_evaluations')->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained('evaluation_competencies')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['interview_evaluation_id', 'competency_id'], 'eval_score_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_evaluation_scores');
    }
};

