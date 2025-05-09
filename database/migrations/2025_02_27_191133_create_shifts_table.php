<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique()->nullable();
            $table->string('shift_label')->nullable();
            $table->string('schedule_in');
            $table->string('schedule_out');
            $table->string('break_start')->nullable();
            $table->string('break_end')->nullable();
            $table->boolean('show_in_request')->default(true);
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
        Schema::dropIfExists('shifts');
    }
}
