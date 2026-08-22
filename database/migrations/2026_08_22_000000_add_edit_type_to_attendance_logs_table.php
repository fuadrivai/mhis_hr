<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE attendance_logs MODIFY type ENUM('check_in', 'check_out', 'edit') NOT NULL");
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->text('description')->nullable()->after('time');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn('description');
        });
        DB::statement("ALTER TABLE attendance_logs MODIFY type ENUM('check_in', 'check_out') NOT NULL");
    }
};
