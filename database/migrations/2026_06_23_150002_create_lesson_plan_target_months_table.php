<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonPlanTargetMonthsTable extends Migration
{
    public function up()
    {
        Schema::create('lesson_plan_target_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_plan_target_id')->constrained('lesson_plan_targets')->onDelete('cascade');
            $table->string('month'); // e.g. "September"
            $table->integer('year');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lesson_plan_target_months');
    }
}
