<?php

namespace Database\Seeders;

use App\Models\TimeoffBalanceGroup;
use App\Models\TimeOff;
use Illuminate\Database\Seeder;

class HourlyTimeOffBalanceSeeder extends Seeder
{
    public function run()
    {
        $group = TimeoffBalanceGroup::firstOrCreate(
            ['code' => 'HOURLY_PERMISSION'],
            [
                'name' => 'Hourly Permission',
                'maximum_hours' => 6,
                'period_type' => 'monthly',
                'is_active' => true,
            ]
        );

        TimeOff::whereIn('code', ['CL1', 'LE', 'LWH'])
            ->whereNull('balance_group_id')
            ->update(['balance_group_id' => $group->id]);
    }
}
