<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('requires_additional_info')->default(false);
            $table->timestamps();
        });

        Schema::create('visitor_purposes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->timestamps();
        });

        $now = now();
        DB::table('visitor_categories')->insert(array_map(
            fn ($name) => [
                'name' => $name,
                'is_active' => true,
                'requires_approval' => false,
                'requires_additional_info' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'Parent / Guardian',
                'Business Partner',
                'Vendor / Supplier',
                'Contractor',
                'Job Applicant',
                'Government Official',
                'Education Partner',
                'Guest / Personal Visitor',
                'Courier / Delivery',
                'Other',
            ]
        ));

        DB::table('visitor_purposes')->insert(array_map(
            fn ($name) => [
                'name' => $name,
                'is_active' => true,
                'requires_approval' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'Business Meeting',
                'Parent Meeting',
                'Student-related Meeting',
                'Academic Meeting',
                'Job Interview',
                'Vendor Meeting',
                'Maintenance / Repair',
                'School Event',
                'Document Submission',
                'Personal Visit',
                'Delivery',
                'Other',
            ]
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_purposes');
        Schema::dropIfExists('visitor_categories');
    }
};
