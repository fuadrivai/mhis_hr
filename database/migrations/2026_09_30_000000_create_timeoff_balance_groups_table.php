<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeoffBalanceGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('timeoff_balance_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('maximum_hours', 8, 2)->nullable();
            $table->string('period_type')->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('timeoff_balance_groups');
    }
}
