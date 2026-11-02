<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id')->constrained('candidate_applications')->cascadeOnDelete();
            $table->foreignId('rejection_reason_id')->constrained('rejection_reasons');
            $table->string('rejected_at_stage', 50);
            $table->text('notes')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['candidate_application_id', 'rejection_reason_id'], 'app_rejections_app_reason_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_rejections');
    }
};
