<?php

namespace Database\Seeders;

use App\Models\RejectionReason;
use Illuminate\Database\Seeder;

class RejectionReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            ['name' => 'Skill mismatch', 'sort_order' => 1],
            ['name' => 'Salary expectation', 'sort_order' => 2],
            ['name' => 'Culture fit', 'sort_order' => 3],
            ['name' => 'Failed psychotest', 'sort_order' => 4],
            ['name' => 'Interview not recommended', 'sort_order' => 5],
            ['name' => 'Position filled', 'sort_order' => 6],
            ['name' => 'Candidate declined', 'sort_order' => 7],
            ['name' => 'Other', 'sort_order' => 8],
        ];

        foreach ($reasons as $reason) {
            RejectionReason::firstOrCreate(
                ['name' => $reason['name']],
                [
                    'is_active' => true,
                    'sort_order' => $reason['sort_order'],
                ]
            );
        }
    }
}

