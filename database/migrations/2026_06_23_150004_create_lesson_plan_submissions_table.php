<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonPlanSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('lesson_plan_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_subject_id')->constrained('employee_subjects')->onDelete('cascade');
            $table->foreignId('lesson_plan_target_month_id')->constrained('lesson_plan_target_months')->onDelete('cascade');
            $table->integer('week_number'); // 1, 2, 3, 4
            $table->string('title')->nullable();
            $table->string('file_link')->nullable();
            $table->string('status')->default('draft'); // draft, submitted, need_revision, approved
            $table->integer('current_approval_level')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lesson_plan_submissions');
    }
}
