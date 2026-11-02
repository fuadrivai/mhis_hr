<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_round_id')->constrained('interview_rounds')->cascadeOnDelete();
            $table->foreignId('interviewer_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('overall_recommendation', ['strongly_recommend', 'recommend', 'hold', 'not_recommend'])->nullable();
            $table->text('strengths')->nullable();
            $table->text('concerns')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('original_attachment_name')->nullable();
            $table->text('reopened_reason')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['interview_round_id', 'interviewer_employee_id'], 'round_eval_unique');
            $table->index(['interviewer_employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_evaluations');
    }
};

