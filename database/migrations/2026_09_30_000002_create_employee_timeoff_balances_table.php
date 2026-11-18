<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeTimeoffBalancesTable extends Migration
{
    public function up()
    {
        Schema::create('employee_timeoff_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('balance_group_id')->constrained('timeoff_balance_groups')->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end')->nullable();
            $table->decimal('allocated_hours', 10, 2)->default(0);
            $table->decimal('used_hours', 10, 2)->default(0);
            $table->decimal('remaining_hours', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['employee_id', 'balance_group_id', 'period_start'], 'employee_timeoff_balance_period_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_timeoff_balances');
    }
}
