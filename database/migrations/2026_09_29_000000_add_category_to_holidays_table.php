<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToHolidaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('holidays') && !Schema::hasColumn('holidays', 'category')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->string('category', 50)->nullable()->after('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('holidays') && Schema::hasColumn('holidays', 'category')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
}
