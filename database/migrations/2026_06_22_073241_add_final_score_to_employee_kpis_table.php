<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinalScoreToEmployeeKpisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_kpis', function (Blueprint $table) {
            $table->decimal('final_score', 8, 2)->nullable()->after('tal_file_url');
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
            $table->dropColumn('final_score');
        });
    }
}
