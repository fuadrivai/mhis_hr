<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeTimeoffBalanceTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('employee_timeoff_balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->foreignId('balance_id');
            $table->foreignId('timeoff_id')->nullable();
            $table->foreignId('approval_request_id')->nullable();
            $table->string('transaction_type', 20);
            $table->decimal('hours', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('employee_id', 'etbt_employee_fk')->references('id')->on('employees')->restrictOnDelete();
            $table->foreign('balance_id', 'etbt_balance_fk')->references('id')->on('employee_timeoff_balances')->restrictOnDelete();
            $table->foreign('timeoff_id', 'etbt_timeoff_fk')->references('id')->on('timeoffs')->nullOnDelete();
            $table->foreign('approval_request_id', 'etbt_approval_request_fk')->references('id')->on('approval_requests')->nullOnDelete();
            $table->unique(['approval_request_id', 'transaction_type'], 'timeoff_balance_request_transaction_unique');
            $table->index(['employee_id', 'balance_id'], 'etbt_employee_balance_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_timeoff_balance_transactions');
    }
}
