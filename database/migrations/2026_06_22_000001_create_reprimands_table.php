<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReprimandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reprimands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('reprimand_type_id')->constrained('reprimand_types')->onDelete('cascade');
            $table->date('effective_date');
            $table->date('end_date');
            $table->text('notes')->nullable();
            $table->string('attachment_link')->nullable();
            $table->integer('document_template_id')->nullable(); // Kept nullable for future document templates
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
        Schema::dropIfExists('reprimands');
    }
}
