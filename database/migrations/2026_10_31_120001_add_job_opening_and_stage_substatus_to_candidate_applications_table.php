<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->foreignId('job_opening_id')->nullable()->after('candidate_id')->constrained('job_openings')->nullOnDelete();
            $table->string('stage_substatus', 50)->nullable()->after('stage');
            $table->index(['job_opening_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->dropForeign(['job_opening_id']);
            $table->dropIndex(['job_opening_id', 'stage']);
            $table->dropColumn(['job_opening_id', 'stage_substatus']);
        });
    }
};

