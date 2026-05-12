<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('location_id');
                $table->string('employee_status')->default('active')->after('is_active')->comment('active, resigned, terminated, end of contract, etc. This is for more detailed status of the employee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('employee_status');
        });
    }
}
