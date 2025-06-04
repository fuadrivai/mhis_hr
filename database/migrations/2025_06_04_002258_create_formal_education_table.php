<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormalEducationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formal_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('degree_id');
            $table->foreignId('employee_id');
            $table->string('institution');
            $table->string('field_of_study')->nullable();
            $table->string('grade')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('activities')->nullable();
            $table->boolean('is_certificate')->default(false);
            $table->string('certificate')->nullable();
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
        Schema::dropIfExists('formal_education');
    }
}
