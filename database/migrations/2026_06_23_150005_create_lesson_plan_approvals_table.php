<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonPlanApprovalsTable extends Migration
{
    public function up()
    {
        Schema::create('lesson_plan_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_plan_submission_id')->constrained('lesson_plan_submissions')->onDelete('cascade');
            $table->foreignId('approver_employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('status'); // approved, need_revision
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lesson_plan_approvals');
    }
}
