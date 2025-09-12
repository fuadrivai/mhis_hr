<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_details', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('location name');
            $table->string('address')->comment('location address')->nullable();
            $table->string('description')->nullable();
            $table->decimal('latitude', 20, 14)->default(0);
            $table->decimal('longitude', 20, 14)->default(0);
            $table->decimal('radius')->default(0)->comment('radius absent position from location base on latitude longitude');
            $table->foreignId('location_id');
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
        Schema::dropIfExists('location_details');
    }
}
