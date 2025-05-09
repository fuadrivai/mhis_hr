<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyOtherInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_other_infos', function (Blueprint $table) {
            $table->id();
            $table->string('hq_initial', 20)->nullable();
            $table->string('hq_code', 20)->nullable();
            $table->boolean('show_branch_name', 100)->default(false);
            $table->decimal('umr', 10, 2);
            $table->string('umr_province')->nullable();
            $table->string('umr_city')->nullable();
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
        Schema::dropIfExists('company_other_infos');
    }
}
