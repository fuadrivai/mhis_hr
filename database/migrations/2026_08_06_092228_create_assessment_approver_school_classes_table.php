<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentApproverSchoolClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_approver_school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_approver_id');
            $table->foreign('assessment_approver_id', 'fk_ass_app_id')->references('id')->on('assessment_approvers')->onDelete('cascade');
            $table->foreignId('school_class_id');
            $table->foreign('school_class_id', 'fk_sch_cls_id')->references('id')->on('school_classes')->onDelete('cascade');
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
        Schema::dropIfExists('assessment_approver_school_classes');
    }
}
