<?php

namespace Tests\Unit;

use App\Models\Employment;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EmploymentLengthOfServiceFilterTest extends TestCase
{
    public function test_length_of_service_ranges_use_expected_date_boundaries(): void
    {
        Carbon::setTestNow('2026-11-17');

        try {
            $ranges = [
                '0_to_5' => ['2026-11-17', '2020-11-17'],
                'less_than_1' => ['2026-11-17', '2025-11-17'],
                '1_to_3' => ['2026-11-17', '2025-11-17', '2022-11-17'],
                '3_to_5' => ['2026-11-17', '2022-11-17', '2020-11-17'],
                '5_to_10' => ['2026-11-17', '2020-11-17', '2015-11-17'],
                'more_than_10' => ['2026-11-17', '2015-11-17'],
            ];

            foreach ($ranges as $range => $expectedBindings) {
                $query = Employment::query()->forLengthOfService($range);

                $this->assertSame($expectedBindings, $query->getBindings(), $range);
                $this->assertStringContainsString('`join_date` is not null', strtolower($query->toSql()));
            }
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_length_of_service_filter_rejects_unknown_ranges(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Employment::query()->forLengthOfService('unknown');
    }
}
