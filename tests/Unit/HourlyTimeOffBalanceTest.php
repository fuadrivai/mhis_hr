<?php

namespace Tests\Unit;

use App\Services\Implement\HourlyTimeOffBalanceImplement;
use PHPUnit\Framework\TestCase;

class HourlyTimeOffBalanceTest extends TestCase
{
    public function test_requested_hours_support_decimal_values_and_time_ranges()
    {
        $service = new HourlyTimeOffBalanceImplement();

        $this->assertSame('0.25', $service->getRequestedHours([
            'start_date' => '2026-09-30',
            'start_time' => '09:00',
            'end_time' => '09:15',
        ]));
        $this->assertSame('0.50', $service->getRequestedHours([
            'start_date' => '2026-09-30',
            'start_time' => '09:00',
            'end_time' => '09:30',
        ]));
        $this->assertSame('1.50', $service->getRequestedHours([
            'start_date' => '2026-09-30',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]));
        $this->assertSame('2.25', $service->getRequestedHours(['hours' => '2.25']));
    }
}
