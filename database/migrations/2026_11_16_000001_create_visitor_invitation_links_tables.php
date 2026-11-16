<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_invitation_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('host_employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('visitor_category_id')->constrained('visitor_categories')->restrictOnDelete();
            $table->foreignId('visitor_purpose_id')->constrained('visitor_purposes')->restrictOnDelete();
            $table->date('visit_date');
            $table->dateTime('expected_arrival_at');
            $table->dateTime('expected_departure_at');
            $table->text('notes')->nullable();
            $table->dateTime('expires_at');
            $table->timestamps();
            $table->index(['created_by', 'visit_date']);
        });

        Schema::create('visitor_invitation_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('visitor_invitation_batches')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->text('token_encrypted');
            $table->string('recipient_email')->nullable();
            $table->foreignId('visitor_visit_id')->nullable()->unique()->constrained('visitor_visits')->nullOnDelete();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['batch_id', 'submitted_at', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_invitation_links');
        Schema::dropIfExists('visitor_invitation_batches');
    }
};
