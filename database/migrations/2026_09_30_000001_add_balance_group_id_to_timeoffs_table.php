<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBalanceGroupIdToTimeoffsTable extends Migration
{
    public function up()
    {
        Schema::table('timeoffs', function (Blueprint $table) {
            $table->foreignId('balance_group_id')
                ->nullable()
                ->after('id')
                ->constrained('timeoff_balance_groups')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('timeoffs', function (Blueprint $table) {
            $table->dropForeign(['balance_group_id']);
            $table->dropColumn('balance_group_id');
        });
    }
}
