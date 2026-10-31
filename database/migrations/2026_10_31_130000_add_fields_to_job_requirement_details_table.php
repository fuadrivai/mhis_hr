<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_requirement_details', function (Blueprint $table) {
            $table->boolean('is_mandatory')->default(false)->after('requirement');
            $table->unsignedTinyInteger('weight')->default(3)->after('is_mandatory');
            $table->string('category', 50)->default('other')->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('job_requirement_details', function (Blueprint $table) {
            $table->dropColumn(['is_mandatory', 'weight', 'category']);
        });
    }
};

