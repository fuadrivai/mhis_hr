<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLiveAttendanceSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('live_attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('need_face_recognition')->default(true);
            $table->timestamps();
        });

        DB::table('live_attendance_settings')->insert([
            'id' => 1,
            'need_face_recognition' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('live_attendance_settings');
    }
}
