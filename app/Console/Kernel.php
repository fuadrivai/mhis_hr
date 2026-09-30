<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('attendance:generate-daily')->dailyAt('00:05');
        $schedule->command('attendance:compress-photos')->dailyAt('01:00')->withoutOverlapping(120);
        $schedule->command('holiday:generate-holiday')->weeklyOn(0, '00:00')
            ->when(function () {
                return now()->format('m-d') !== '01-01';
            })
            ->withoutOverlapping();
        $schedule->command('holiday:generate-holiday')
            ->yearlyOn(1, 1, '00:00')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
