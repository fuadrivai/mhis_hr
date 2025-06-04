<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFamiliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->foreignId('personal_id');
            $table->foreignId('relation_ship_id');
            $table->foreignId('religion_id');
            $table->string('mobile_number')->nullable();
            $table->string('address')->nullable();
            $table->string('id_number')->nullable();
            $table->enum('gendre', ['1', '2']);
            $table->enum('marital_status', ['1', '2', '3', '4'])->nullable();
            $table->date('birth_date');
            $table->string('job')->nullable();
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
        Schema::dropIfExists('families');
    }
}
