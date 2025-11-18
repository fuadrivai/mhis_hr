<?php

namespace App\Providers;

use App\Services\AttendanceLogService;
use App\Services\Implement\AttendanceLogImplement;
use Illuminate\Support\ServiceProvider;

class AttendanceLogProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public array $singletons = [
        AttendanceLogService::class => AttendanceLogImplement::class
    ];
    public function provides(): array
    {
        return [AttendanceLogService::class];
    }
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
