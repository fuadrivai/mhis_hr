<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('kpi_template_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_template_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'managerial' or 'tal'
            $table->string('name');
            $table->decimal('weight', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('kpi_template_sub_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_template_target_id')->constrained('kpi_template_targets')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('employee_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('academic_year')->nullable();
            $table->decimal('reprimand_deduction_percentage', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('employee_kpi_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_kpi_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'managerial' or 'tal'
            $table->string('name');
            $table->decimal('weight', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('employee_kpi_sub_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_kpi_target_id')->constrained('employee_kpi_targets')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight', 5, 2)->nullable();
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
        Schema::dropIfExists('employee_kpi_sub_targets');
        Schema::dropIfExists('employee_kpi_targets');
        Schema::dropIfExists('employee_kpis');
        Schema::dropIfExists('kpi_template_sub_targets');
        Schema::dropIfExists('kpi_template_targets');
        Schema::dropIfExists('kpi_templates');
    }
}
