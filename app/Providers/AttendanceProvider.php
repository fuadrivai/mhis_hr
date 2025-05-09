<?php

namespace App\Providers;

use App\Services\AttendanceService;
use App\Services\Implement\AttendanceImplement;
use Illuminate\Support\ServiceProvider;

class AttendanceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public array $singletons = [
        AttendanceService::class => AttendanceImplement::class
    ];
    public function provides(): array
    {
        return [AttendanceService::class];
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
