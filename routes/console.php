<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:generate-daily')->dailyAt('00:05');

Schedule::command('visitor:mark-no-shows')->dailyAt('00:20')->withoutOverlapping();

Schedule::command('holiday:generate-holiday')
    ->weeklyOn(0, '00:00')
    ->when(fn () => now()->format('m-d') !== '01-01')
    ->withoutOverlapping();

Schedule::command('holiday:generate-holiday')
    ->yearlyOn(1, 1, '00:00')
    ->withoutOverlapping();

Schedule::command('attendance:compress-photos')
    ->dailyAt('01:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(120)
    ->appendOutputTo(storage_path('logs/attendance-compress.log'));

Schedule::command('attendance:compress-photos')
    ->dailyAt('12:15')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(120)
    ->appendOutputTo(storage_path('logs/attendance-compress.log'));
