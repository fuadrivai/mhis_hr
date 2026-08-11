<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentApproverSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_approver_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_approver_id')->constrained('assessment_approvers')->onDelete('cascade')->name('fk_as_ap_sb_ap_id');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade')->name('fk_as_ap_sb_sb_id');
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
        Schema::dropIfExists('assessment_approver_subjects');
    }
}
