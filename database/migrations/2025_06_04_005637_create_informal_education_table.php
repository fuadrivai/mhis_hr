<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInformalEducationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('informal_education', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('held_by')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('duration_type', ['day', 'hour']);
            $table->integer('duration')->nullable()->default(1);
            $table->date('expired_date')->nullable();
            $table->decimal('fee', 10, 2);
            $table->string('activities')->nullable();
            $table->boolean('is_certificate')->default(false);
            $table->string('certificate')->nullable();
            $table->foreignId('employee_id');
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
        Schema::dropIfExists('informal_education');
    }
}
