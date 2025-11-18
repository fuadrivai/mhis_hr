<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->comment('Employee ID');
            $table->foreignId('user_id')->comment('User ID');
            $table->date('date');
            $table->string('status')->default('present');
            $table->string('fullname')->comment('Full Name of employee');
            $table->string('shift_name')->comment('Shift Name of employee');
            $table->boolean('holiday')->comment('Is Holiday?')->default(false);
            $table->string('schedule_in')->comment('Schedule In time')->nullable();
            $table->string('schedule_out')->comment('Schedule Out time')->nullable();

            // Check-in
            $table->dateTime('check_in')->nullable();
            $table->string('check_in_photo')->nullable();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->decimal('check_in_radius', 8, 2)->nullable();

            // Check-out
            $table->dateTime('check_out')->nullable();
            $table->string('check_out_photo')->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->decimal('check_out_radius', 8, 2)->nullable();

            $table->timestamps();
            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
