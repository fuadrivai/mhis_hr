<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileUrlsToEmployeeKpisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_kpis', function (Blueprint $table) {
            $table->string('managerial_file_url')->nullable()->after('reprimand_deduction_percentage');
            $table->string('tal_file_url')->nullable()->after('managerial_file_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_kpis', function (Blueprint $table) {
            $table->dropColumn(['managerial_file_url', 'tal_file_url']);
        });
    }
}
