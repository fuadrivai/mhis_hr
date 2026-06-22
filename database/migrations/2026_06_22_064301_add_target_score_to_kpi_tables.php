<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTargetScoreToKpiTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kpi_template_targets', function (Blueprint $table) {
            $table->decimal('target_score', 8, 2)->nullable()->after('name');
        });

        Schema::table('kpi_template_sub_targets', function (Blueprint $table) {
            $table->decimal('target_score', 8, 2)->nullable()->after('name');
        });

        Schema::table('employee_kpi_targets', function (Blueprint $table) {
            $table->decimal('target_score', 8, 2)->nullable()->after('name');
        });

        Schema::table('employee_kpi_sub_targets', function (Blueprint $table) {
            $table->decimal('target_score', 8, 2)->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kpi_template_targets', function (Blueprint $table) {
            $table->dropColumn('target_score');
        });

        Schema::table('kpi_template_sub_targets', function (Blueprint $table) {
            $table->dropColumn('target_score');
        });

        Schema::table('employee_kpi_targets', function (Blueprint $table) {
            $table->dropColumn('target_score');
        });

        Schema::table('employee_kpi_sub_targets', function (Blueprint $table) {
            $table->dropColumn('target_score');
        });
    }
}
