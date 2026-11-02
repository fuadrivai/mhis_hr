<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_round_interviewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_round_id')->constrained('interview_rounds')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['interview_round_id', 'employee_id'], 'round_interviewer_unique');
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_round_interviewers');
    }
};

