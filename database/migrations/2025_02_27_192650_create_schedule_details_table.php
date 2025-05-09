<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_details', function (Blueprint $table) {
            $table->id();
            $table->string('day');
            $table->foreignId('schedule_id');
            $table->foreignId('shift_id');
            $table->string('shift_name');
            $table->string('working_hour');
            $table->string('break_hour');
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
        Schema::dropIfExists('schedule_details');
    }
}
