<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->dropForeign(['registered_by']);
            $table->unsignedBigInteger('registered_by')->nullable()->change();
            $table->foreign('registered_by')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('visitor_visits')
            ->whereNull('registered_by')
            ->update([
                'registered_by' => DB::raw('(SELECT employees.user_id FROM employees WHERE employees.id = visitor_visits.host_employee_id)'),
            ]);

        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->dropForeign(['registered_by']);
            $table->unsignedBigInteger('registered_by')->nullable(false)->change();
            $table->foreign('registered_by')->references('id')->on('users')->restrictOnDelete();
        });
    }
};
