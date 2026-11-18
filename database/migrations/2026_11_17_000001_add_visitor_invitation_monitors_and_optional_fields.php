<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->foreignId('visitor_category_id')->nullable()->change();
            $table->foreignId('visitor_purpose_id')->nullable()->change();
        });

        Schema::create('visitor_visit_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_visit_id')->constrained('visitor_visits')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['visitor_visit_id', 'employee_id']);
            $table->index(['employee_id', 'visitor_visit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_visit_monitors');

        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->foreignId('visitor_category_id')->nullable(false)->change();
            $table->foreignId('visitor_purpose_id')->nullable(false)->change();
        });
    }
};
