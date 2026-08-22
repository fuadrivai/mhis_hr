<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE attendance_logs MODIFY type ENUM('check_in', 'check_out', 'edit', 'delete', 'lock', 'unlock') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendance_logs MODIFY type ENUM('check_in', 'check_out', 'edit', 'delete', 'lock') NOT NULL");
    }
};
