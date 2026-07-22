<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timeoff_id')->constrained()->cascadeOnDelete();
            $table->integer('total')->default(0)->comment('Total leave allocation for the employee');
            $table->integer('used')->default(0)->comment('Total leave used by the employee');
            $table->integer('remaining')->default(0)->comment('Remaining leave balance for the employee');
            $table->foreignId('academic_year_id')->constrained('academic_years')
                ->cascadeOnDelete()
                ->comment('Reference to the academic year for which the leave allocation is applicable');
            $table->timestamps();

            $table->unique([
                'employee_id',
                'timeoff_id',
                'academic_year_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_allocations');
    }
}
