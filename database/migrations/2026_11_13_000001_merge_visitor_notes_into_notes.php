<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('visitor_visits')
            ->select(['id', 'notes', 'visitor_notes'])
            ->whereNotNull('visitor_notes')
            ->orderBy('id')
            ->chunkById(100, function ($visits) {
                foreach ($visits as $visit) {
                    $visitorNotes = trim($visit->visitor_notes);
                    if ($visitorNotes === '') {
                        continue;
                    }

                    $notes = $visit->notes;
                    DB::table('visitor_visits')
                        ->where('id', $visit->id)
                        ->update([
                            'notes' => $notes === null || trim($notes) === ''
                                ? $visitorNotes
                                : $notes . "\n" . $visitorNotes,
                        ]);
                }
            });

        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->dropColumn('visitor_notes');
        });
    }

    public function down(): void
    {
        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->text('visitor_notes')->nullable();
        });
    }
};
