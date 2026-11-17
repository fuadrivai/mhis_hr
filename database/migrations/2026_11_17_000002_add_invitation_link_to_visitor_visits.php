<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->foreignId('visitor_invitation_link_id')
                ->nullable()
                ->after('registered_by')
                ->constrained('visitor_invitation_links')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->dropForeign(['visitor_invitation_link_id']);
            $table->dropColumn('visitor_invitation_link_id');
        });
    }
};
