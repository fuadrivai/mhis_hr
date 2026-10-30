<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->string('division')->nullable()->after('position_applied');
            $table->string('stage', 30)->default('received')->after('status');
            $table->foreignId('hr_pic_id')->nullable()->after('registered_by')->constrained('users')->nullOnDelete();
            $table->timestamp('last_activity_at')->nullable()->after('applied_at');
            $table->index(['division', 'stage', 'hr_pic_id'], 'candidate_applications_pipeline_index');
            $table->index(['last_activity_at', 'applied_at'], 'candidate_applications_activity_index');
        });

        DB::table('candidate_applications')
            ->whereNull('last_activity_at')
            ->update(['last_activity_at' => DB::raw('COALESCE(updated_at, applied_at)')]);
    }

    public function down(): void
    {
        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->dropIndex('candidate_applications_pipeline_index');
            $table->dropIndex('candidate_applications_activity_index');
            $table->dropConstrainedForeignId('hr_pic_id');
            $table->dropColumn(['division', 'stage', 'last_activity_at']);
        });
    }
};
