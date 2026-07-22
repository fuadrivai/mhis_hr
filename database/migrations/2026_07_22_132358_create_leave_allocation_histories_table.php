<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveAllocationHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_allocation_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_allocation_id');
            $table->foreign('leave_allocation_id')->references('id')->on('leave_allocations')->onDelete('cascade');
            $table->string('type')->comment('Type of change: allocated, adjustment,expired');
            $table->integer('days')->comment('Amount of leave allocated or adjusted');
            $table->string('remark')->nullable()->comment('Reason for the change');
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
        Schema::dropIfExists('leave_allocation_histories');
    }
}
