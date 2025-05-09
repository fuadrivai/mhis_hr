<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('address')->nullable();
            $table->string('province', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('ukuran_perusahaan', 100)->nullable();
            $table->string('logo')->nullable();
            $table->foreignId('company_info_id');
            $table->foreignId('company_tax_info_id');
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
        Schema::dropIfExists('companies');
    }
}
