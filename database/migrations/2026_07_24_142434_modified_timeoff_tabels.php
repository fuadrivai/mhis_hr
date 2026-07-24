<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifiedTimeoffTabels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timeoffs', function (Blueprint $table) {
            $table->string('eligibility_type')->default('all')->comment('all, rule_bases, assigned')->after('is_global');
            $table->json('eligibility_rules')->after('eligibility_type')->nullable();
            $table->string('usage_type')->default('multiple')->after('eligibility_rules')->comment('once_only, once_per_year, multiple');
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
            $table->dropColumn('eligibility_type');
            $table->dropColumn('eligibility_rules');
            $table->dropColumn('usage_type');
        });
    }
}
