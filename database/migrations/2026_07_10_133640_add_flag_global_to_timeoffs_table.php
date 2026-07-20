<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlagGlobalToTimeoffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timeoffs', function (Blueprint $table) {
            $table->boolean('is_global')->default(true)->after('is_active');
            $table->boolean('deduct_leave_balance')->default(false)->after('is_global');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timeoffs', function (Blueprint $table) {
            $table->dropColumn('is_global');
            $table->dropColumn('deduct_leave_balance');
        });
    }
}
