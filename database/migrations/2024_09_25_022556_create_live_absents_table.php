<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveAbsentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_absents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('fullname');
            $table->foreignId('pin_locations_id');
            $table->string('location_name');
            $table->string('coordinate');
            $table->string('note')->nullable();
            $table->timestamp('clock_time');
            $table->decimal('latitude', 20, 14);
            $table->decimal('longitude', 20, 14);
            $table->integer('absent_type'); //1=solat asar
            $table->integer('radius')->default(0);
            $table->decimal('distance', 10, 2);
            $table->enum('event_type', ['clock_in', 'clock_out']);
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
        Schema::dropIfExists('live_absents');
    }
}
