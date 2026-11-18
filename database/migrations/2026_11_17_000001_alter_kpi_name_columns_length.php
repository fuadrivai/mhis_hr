<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterKpiNameColumnsLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'kpi_templates' => 'name',
            'kpi_template_targets' => 'name',
            'kpi_template_sub_targets' => 'name',
            'employee_kpi_targets' => 'name',
            'employee_kpi_sub_targets' => 'name',
        ];

        foreach ($tables as $table => $column) {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` VARCHAR(500)");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'kpi_templates' => 'name',
            'kpi_template_targets' => 'name',
            'kpi_template_sub_targets' => 'name',
            'employee_kpi_targets' => 'name',
            'employee_kpi_sub_targets' => 'name',
        ];

        foreach ($tables as $table => $column) {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` VARCHAR(255)");
        }
    }
}
