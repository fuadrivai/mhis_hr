<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id')->constrained('candidate_applications')->cascadeOnDelete();
            $table->unsignedInteger('round_no')->default(1);
            $table->enum('round_type', ['hr', 'user', 'panel'])->default('hr');
            $table->dateTime('scheduled_at');
            $table->text('location_or_link')->nullable();
            $table->enum('status', ['scheduled', 'done', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['candidate_application_id', 'round_no']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_rounds');
    }
};

