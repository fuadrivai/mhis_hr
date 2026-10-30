<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_requirement_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_requirement_id')->constrained()->cascadeOnDelete();
            $table->text('requirement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_requirement_details');
    }
};
