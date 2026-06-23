<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectCategoryApproversTable extends Migration
{
    public function up()
    {
        Schema::create('subject_category_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_category_id')->constrained('subject_categories')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->integer('level');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subject_category_approvers');
    }
}
