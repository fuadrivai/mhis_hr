<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateHoliday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holiday:generate-holiday';
    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate government holidays for the current year';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return $this->syncGovernmentHolidays();
    }

    private function syncGovernmentHolidays(): int
    {
        $year = now()->year;

        $this->info("Syncing government holidays for {$year}...");
        $this->line('');
        $this->line('Fetching holiday data from API...');

        try {
            $apiHolidays = $this->fetchGovernmentHolidays($year);
            $this->line('API holidays: ' . count($apiHolidays));

            $counts = DB::transaction(function () use ($year, $apiHolidays) {
                $existingHolidays = Holiday::whereYear('date', $year)
                    ->where('type', Holiday::TYPE_GOVERNMENT)
                    ->get()
                    ->keyBy(function (Holiday $holiday) {
                        return $holiday->date->format('Y-m-d');
                    });

                $this->line('Existing government holidays: ' . $existingHolidays->count());

                $inserted = 0;
                $updated = 0;
                $deleted = 0;

                foreach ($apiHolidays as $date => $attributes) {
                    $holiday = $existingHolidays->get($date);

                    if (!$holiday) {
                        Holiday::create($attributes);
                        $inserted++;
                        continue;
                    }

                    $hasChanges = $holiday->description !== $attributes['description']
                        || $holiday->name !== $attributes['name']
                        || $holiday->category !== null
                        || $holiday->is_active !== true
                        || $holiday->branch_id !== null;

                    if ($hasChanges) {
                        $holiday->fill($attributes)->save();
                        $updated++;
                    }
                }

                foreach ($existingHolidays as $date => $holiday) {
                    if (!array_key_exists($date, $apiHolidays)) {
                        $holiday->delete();
                        $deleted++;
                    }
                }

                return compact('inserted', 'updated', 'deleted');
            });

            $this->line('');
            $this->info('Inserted: ' . $counts['inserted']);
            $this->info('Updated: ' . $counts['updated']);
            $this->info('Deleted: ' . $counts['deleted']);
            $this->line('');
            $this->info('Government holidays synchronized successfully.');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            Log::error('Government holiday synchronization failed.', [
                'year' => $year,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            $this->error("Failed to synchronize government holidays for {$year}.");
            $this->line('Existing holiday data has not been modified.');

            return self::FAILURE;
        }
    }

    private function fetchGovernmentHolidays(int $year): array
    {
        $url = config('services.holiday.url');
        if (!is_string($url) || trim($url) === '') {
            throw new \RuntimeException('The holiday API URL is not configured.');
        }

        $response = Http::acceptJson()
            ->timeout(15)
            ->get($url, ['year' => $year]);

        if (!$response->successful()) {
            throw new \RuntimeException('The holiday API returned HTTP ' . $response->status() . '.');
        }

        $holidays = $response->json();
        if (is_array($holidays) && array_key_exists('data', $holidays)) {
            $holidays = $holidays['data'];
        }

        if (!is_array($holidays) || $holidays === [] || array_keys($holidays) !== range(0, count($holidays) - 1)) {
            throw new \RuntimeException('The holiday API returned an empty or unexpected response.');
        }

        $normalizedHolidays = [];
        foreach ($holidays as $holiday) {
            if (!is_array($holiday)
                || !isset($holiday['date'], $holiday['description'])
                || !is_string($holiday['date'])
                || !is_string($holiday['description'])
                || trim($holiday['description']) === '') {
                throw new \RuntimeException('The holiday API returned an invalid holiday record.');
            }

            $parsedDate = \DateTime::createFromFormat('!Y-m-d', $holiday['date']);
            if (!$parsedDate
                || $parsedDate->format('Y-m-d') !== $holiday['date']
                || $parsedDate->format('Y') !== (string) $year) {
                throw new \RuntimeException('The holiday API returned an invalid date for the requested year.');
            }

            if (array_key_exists($holiday['date'], $normalizedHolidays)) {
                throw new \RuntimeException('The holiday API returned duplicate holiday dates.');
            }

            $normalizedHolidays[$holiday['date']] = [
                'type' => Holiday::TYPE_GOVERNMENT,
                'category' => null,
                'description' => $holiday['description'],
                'branch_id' => null,
                'date' => $holiday['date'],
                'name' => $holiday['description'],
                'is_active' => true,
            ];
        }
        return $normalizedHolidays;
    }
}
