<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_submission_id')->constrained('assessment_submissions')->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('employees')->onDelete('cascade');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->integer('level');
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
        Schema::dropIfExists('assessment_approvals');
    }
}
