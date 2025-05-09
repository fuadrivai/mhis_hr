<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmploymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employments', function (Blueprint $table) {
            $table->id();
            $table->string("employee_id")->nullable();
            $table->foreignId("organization_id")->nullable();
            $table->string("organization_name")->nullable();
            $table->foreignId("job_position_id")->nullable();
            $table->string("job_position_name")->nullable();
            $table->string("approval_line")->nullable();
            $table->foreignId("job_level_id")->nullable();
            $table->string("job_level_name")->nullable();
            $table->foreignId("branch_id")->nullable();
            $table->string("branch_name")->nullable();
            $table->enum('employment_status', ['permanent', 'contract', 'probation', 'freelance']);
            $table->date("join_date")->nullable();
            $table->date("end_date")->nullable();
            $table->date("resign_date")->nullable();
            $table->date("sign_date")->nullable();
            $table->boolean("status")->default(true);
            $table->string("nationality_code")->nullable();
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
        Schema::dropIfExists('employments');
    }
}
