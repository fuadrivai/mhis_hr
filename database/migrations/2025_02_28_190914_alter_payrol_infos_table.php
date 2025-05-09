<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPayrolInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payrol_infos', function (Blueprint $table) {
            $table->foreignId('bank_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('npwp')->nullable();
            $table->string('nik')->nullable();
            $table->string('PTKP_status')->nullable();
            $table->string('bpjs_ketenagakerjaan')->nullable();
            $table->string('bpjs_kesehatan')->nullable();
            $table->string('employment_tax_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payrol_infos', function (Blueprint $table) {
            //
        });
    }
}
