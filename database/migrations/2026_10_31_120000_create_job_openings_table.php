<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_requirement_id')->nullable()->constrained('job_requirements')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('title');
            $table->string('division', 100)->nullable();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->unsignedInteger('headcount')->default(1);
            $table->foreignId('hiring_manager_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('opened_at');
            $table->date('closes_at')->nullable();
            $table->string('status', 30)->default('open');
            $table->text('closed_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['status', 'opened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_openings');
    }
};

