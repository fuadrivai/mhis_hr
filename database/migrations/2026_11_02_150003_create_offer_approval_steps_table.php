<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->unsignedInteger('level')->default(1);
            $table->foreignId('approver_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'skipped'])->default('pending');
            $table->timestamp('acted_at')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['offer_id', 'level']);
            $table->index(['approver_employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_approval_steps');
    }
};

