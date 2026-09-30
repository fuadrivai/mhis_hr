<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHolidayIdToEmployeeShiftOverridesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_shift_overrides')
            && !Schema::hasColumn('employee_shift_overrides', 'holiday_id')) {
            Schema::table('employee_shift_overrides', function (Blueprint $table) {
                $table->foreignId('holiday_id')
                    ->nullable()
                    ->after('shift_id')
                    ->constrained('holidays')
                    ->cascadeOnDelete();
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
        if (Schema::hasTable('employee_shift_overrides')
            && Schema::hasColumn('employee_shift_overrides', 'holiday_id')) {
            Schema::table('employee_shift_overrides', function (Blueprint $table) {
                $table->dropForeign(['holiday_id']);
                $table->dropColumn('holiday_id');
            });
        }
    }
}
