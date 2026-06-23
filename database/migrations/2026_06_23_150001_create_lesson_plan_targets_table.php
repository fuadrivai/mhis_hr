<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonPlanTargetsTable extends Migration
{
    public function up()
    {
        Schema::create('lesson_plan_targets', function (Blueprint $table) {
            $table->id();
            $table->date('deadline_date');
            $table->string('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lesson_plan_targets');
    }
}
