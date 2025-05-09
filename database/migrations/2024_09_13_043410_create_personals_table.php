<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personals', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('barcode')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('address')->nullable();
            $table->string('current_address')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->enum('gendre', ['1', '2'])->nullable();
            $table->enum('marital_status', ['1', '2', '3', '4'])->nullable();
            $table->enum('blood_type', ['A', 'B', 'AB', 'O'])->nullable();
            $table->foreignId('religion_id');
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
        Schema::dropIfExists('personals');
    }
}
