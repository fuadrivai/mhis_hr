<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_code')->unique();
            $table->foreignId('visitor_id')->constrained('visitors')->restrictOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('host_employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('visitor_category_id')->constrained('visitor_categories')->restrictOnDelete();
            $table->foreignId('visitor_purpose_id')->constrained('visitor_purposes')->restrictOnDelete();
            $table->foreignId('registered_by')->constrained('users')->restrictOnDelete();
            $table->string('registration_method', 40)->default('employee_invitation');
            $table->date('visit_date');
            $table->dateTime('expected_arrival_at')->nullable();
            $table->dateTime('expected_departure_at')->nullable();
            $table->dateTime('check_in_at')->nullable();
            $table->foreignId('check_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('check_in_method', 40)->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->foreignId('check_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('check_out_method', 40)->nullable();
            $table->string('badge_number', 40)->nullable();
            $table->string('badge_return_status', 40)->nullable();
            $table->string('check_in_photo')->nullable();
            $table->string('status', 40)->default('expected')->index();
            $table->string('qr_token_hash', 64)->unique();
            $table->text('notes')->nullable();
            $table->text('visitor_notes')->nullable();
            $table->text('check_in_notes')->nullable();
            $table->text('check_out_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['branch_id', 'visit_date', 'status']);
            $table->index(['host_employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_visits');
    }
};
