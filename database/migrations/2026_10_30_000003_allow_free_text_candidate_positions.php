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
            $table->string('position_applied')->nullable()->after('position_id');
            $table->foreignId('position_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('candidate_applications')->whereNull('position_id')->exists()) {
            throw new RuntimeException(
                'Cannot roll back free-text candidate positions while applications have no matching position record.'
            );
        }

        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable(false)->change();
            $table->dropColumn('position_applied');
        });
    }
};
