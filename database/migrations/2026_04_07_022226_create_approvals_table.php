<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained()->cascadeOnDelete();
            $table->integer('step_order');
            $table->foreignId('approver_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('approval_mode', ['any', 'all'])->default('any');
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'skipped',
                'cancelled'
            ])->default('pending');
            $table->timestamp('actioned_date')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('approvals');
    }
}
