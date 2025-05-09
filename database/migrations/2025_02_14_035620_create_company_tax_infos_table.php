<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyTaxInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_tax_infos', function (Blueprint $table) {
            $table->id();
            $table->string('NPWP_old', 30)->unique()->nullable();
            $table->string('NPWP_new', 30)->unique()->nullable();
            $table->string('nitku', 30)->nullable();
            $table->string('taxable_date')->nullable();
            $table->string('tax_person_name', 100)->nullable();
            $table->string('tax_person_npwp_old', 30)->nullable();
            $table->string('tax_person_npwp_new', 30)->nullable();
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
        Schema::dropIfExists('company_tax_infos');
    }
}
