<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_rule_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('e.g. Manager Approval, Director Approval, etc.');
            $table->integer('step_order');
            $table->foreignId('approver_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('approval_mode', ['any', 'all'])->default('any')->comment('any: any approver can approve, all: all approvers must approve');
            $table->timestamps();
            $table->index(['approval_rule_id', 'step_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_steps');
    }
}
