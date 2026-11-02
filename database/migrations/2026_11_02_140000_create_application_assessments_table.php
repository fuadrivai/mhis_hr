<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id')->constrained('candidate_applications')->cascadeOnDelete();
            $table->string('type', 50)->default('psychotest');
            $table->date('assessed_at');
            $table->enum('conclusion', ['recommended', 'considered', 'not_recommended']);
            $table->text('notes')->nullable();
            $table->string('file_path');
            $table->string('original_filename');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['candidate_application_id', 'type']);
            $table->index(['conclusion', 'assessed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_assessments');
    }
};

