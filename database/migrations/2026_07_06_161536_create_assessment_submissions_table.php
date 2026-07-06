<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_assignment_id')->constrained('assessment_assignments')->onDelete('cascade');
            $table->foreignId('assessment_target_id')->constrained('assessment_targets')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->string('file_link')->nullable();
            $table->string('status')->default('draft');
            $table->integer('current_approval_level')->default(1);
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
        Schema::dropIfExists('assessment_submissions');
    }
}
