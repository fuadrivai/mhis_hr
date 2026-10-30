<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions');
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('source', 100);
            $table->string('source_detail')->nullable();
            $table->string('resume_path')->nullable();
            $table->timestamp('consent_at');
            $table->string('consent_version', 50);
            $table->string('registration_method', 30)->default('hr');
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('received');
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->index(['position_id', 'branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_applications');
    }
};
