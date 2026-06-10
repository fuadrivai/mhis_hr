<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToAttendanceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->index('employee_id');
            $table->index('attendance_id');
            $table->index('clock_datetime');
            $table->index(['employee_id', 'clock_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['attendance_id']);
            $table->dropIndex(['clock_datetime']);
            $table->dropIndex(['employee_id', 'clock_datetime']);
        });
    }
}
