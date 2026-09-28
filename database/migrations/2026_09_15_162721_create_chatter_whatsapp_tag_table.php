<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatterWhatsappTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatter_whatsapp_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_chatter_id')->constrained('whatsapp_chatters')->cascadeOnDelete();
            $table->foreignId('whatsapp_tag_id')->constrained('whatsapp_tags')->cascadeOnDelete();
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
        Schema::dropIfExists('chatter_whatsapp_tag');
    }
}
