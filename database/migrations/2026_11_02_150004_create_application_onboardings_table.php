<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_onboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id')->unique()->constrained('candidate_applications')->cascadeOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained('offers')->nullOnDelete();
            $table->enum('contract_status', ['pending', 'sent', 'signed'])->default('pending');
            $table->timestamp('contract_sent_at')->nullable();
            $table->timestamp('contract_signed_at')->nullable();
            $table->string('contract_file_path')->nullable();
            $table->text('contract_url')->nullable();
            $table->date('join_date')->nullable();
            $table->enum('join_status', ['pending', 'joined', 'no_show', 'postponed'])->default('pending');
            $table->date('postponed_to')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();

            $table->index(['candidate_application_id', 'contract_status'], 'app_onboard_app_contract_idx');
            $table->index(['candidate_application_id', 'join_status'], 'app_onboard_app_join_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_onboardings');
    }
};
