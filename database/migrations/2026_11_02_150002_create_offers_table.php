<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id')->constrained('candidate_applications')->cascadeOnDelete();
            $table->foreignId('job_opening_id')->nullable()->constrained('job_openings')->nullOnDelete();
            $table->string('position_title');
            $table->string('grade_level')->nullable();
            $table->text('basic_salary'); // Encrypted cast
            $table->text('allowances')->nullable(); // Encrypted cast (JSON array)
            $table->text('other_benefits')->nullable();
            $table->date('proposed_start_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('offer_letter_url')->nullable(); // Link to PDF/Google Drive file
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'sent',
                'accepted',
                'declined',
                'negotiating',
                'expired',
                'cancelled',
            ])->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->text('notes')->nullable();
            $table->text('decline_reason')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['candidate_application_id', 'status']);
            $table->index(['job_opening_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};

