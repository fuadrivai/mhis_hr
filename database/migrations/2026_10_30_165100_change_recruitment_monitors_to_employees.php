<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('recruitment_monitors', 'employee_id')) {
            Schema::table('recruitment_monitors', function (Blueprint $table) {
                $table->foreignId('employee_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            });
        }

        $assignments = DB::table('recruitment_monitors')
            ->join('employees', 'employees.user_id', '=', 'recruitment_monitors.user_id')
            ->join('employments', 'employments.id', '=', 'employees.employment_id')
            ->where('employments.status', true)
            ->get(['recruitment_monitors.id', 'employees.id as employee_id']);

        foreach ($assignments as $assignment) {
            DB::table('recruitment_monitors')
                ->where('id', $assignment->id)
                ->update(['employee_id' => $assignment->employee_id]);
        }

        DB::table('recruitment_monitors')->whereNull('employee_id')->delete();

        Schema::table('recruitment_monitors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique('recruitment_monitors_user_id_unique');
            $table->dropColumn('user_id');
            $table->foreignId('employee_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_monitors', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
        });

        $assignments = DB::table('recruitment_monitors')
            ->join('employees', 'employees.id', '=', 'recruitment_monitors.employee_id')
            ->whereNotNull('employees.user_id')
            ->get(['recruitment_monitors.id', 'employees.user_id']);

        foreach ($assignments as $assignment) {
            DB::table('recruitment_monitors')
                ->where('id', $assignment->id)
                ->update(['user_id' => $assignment->user_id]);
        }

        DB::table('recruitment_monitors')->whereNull('user_id')->delete();

        Schema::table('recruitment_monitors', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropUnique('recruitment_monitors_employee_id_unique');
            $table->dropColumn('employee_id');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
