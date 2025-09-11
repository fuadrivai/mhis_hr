<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableEmployeeSchedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->foreignId('schedule_id');
            $table->string('schedule_name')->comment('The name of the schedule assigned to the employee');
            $table->date('effective_start_date')->comment('The date when the schedule becomes effective for the employee');
            $table->date('effective_end_date')->comment('The date when the schedule ends for the employee')->nullable();
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
        Schema::dropIfExists('employee_schedule');
    }
}
