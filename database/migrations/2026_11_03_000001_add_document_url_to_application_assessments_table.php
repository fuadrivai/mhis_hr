<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_assessments', function (Blueprint $table) {
            $table->string('document_url', 2048)->nullable()->after('notes');
            $table->string('file_path')->nullable()->change();
            $table->string('original_filename')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('application_assessments', function (Blueprint $table) {
            $table->dropColumn('document_url');
            $table->string('file_path')->nullable(false)->change();
            $table->string('original_filename')->nullable(false)->change();
        });
    }
};

