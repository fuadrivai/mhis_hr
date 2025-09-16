<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->comment('Employee ID');
            $table->foreignId('attendance_id')->comment('Attendance ID');
            $table->enum('type', ['check_in', 'check_out']);
            $table->timestamp('clock_datetime')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->date('clock_date')->comment('Date of clock in/out');
            $table->string('time')->comment('Time of clock in/out');
            $table->boolean('has_location')->default(true);

            $table->string('fullname')->comment('Full Name of employee');
            $table->string('shift_name')->comment('Shift Name of employee');
            $table->string('photo')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('radius', 8, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_logs');
    }
}
