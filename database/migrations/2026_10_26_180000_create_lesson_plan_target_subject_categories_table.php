<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonPlanTargetSubjectCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_plan_target_subject_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_plan_target_id')
                ->constrained('lesson_plan_targets')
                ->onDelete('cascade')
                ->name('fk_lpt_sc_target_id');
            $table->foreignId('subject_category_id')
                ->constrained('subject_categories')
                ->onDelete('cascade')
                ->name('fk_lpt_sc_category_id');
            $table->timestamps();

            $table->unique(['lesson_plan_target_id', 'subject_category_id'], 'lp_target_cat_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lesson_plan_target_subject_categories');
    }
}
