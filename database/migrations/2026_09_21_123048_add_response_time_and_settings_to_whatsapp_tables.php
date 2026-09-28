<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResponseTimeAndSettingsToWhatsappTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $table->time('working_hour_start')->nullable();
            $table->time('working_hour_end')->nullable();
            $table->json('working_days')->nullable();
        });

        Schema::table('whatsapp_reply_logs', function (Blueprint $table) {
            $table->integer('response_time_seconds')->nullable();
            $table->string('incoming_msg_timestamp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsapp_reply_logs', function (Blueprint $table) {
            $table->dropColumn(['response_time_seconds', 'incoming_msg_timestamp']);
        });

        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $table->dropColumn(['working_hour_start', 'working_hour_end', 'working_days']);
        });
    }
}
