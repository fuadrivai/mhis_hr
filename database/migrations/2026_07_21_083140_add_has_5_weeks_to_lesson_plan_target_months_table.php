<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHas5WeeksToLessonPlanTargetMonthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lesson_plan_target_months', function (Blueprint $table) {
            $table->boolean('has_5_weeks')->default(false)->after('year');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lesson_plan_target_months', function (Blueprint $table) {
            $table->dropColumn('has_5_weeks');
        });
    }
}
