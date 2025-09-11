<?php

namespace App\Providers;

use App\Services\EmployeeScheduleService;
use App\Services\Implement\EmployeeScheduleImplement;
use Illuminate\Support\ServiceProvider;

class EmployeeScheduleProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public array $singletons = [
        EmployeeScheduleService::class => EmployeeScheduleImplement::class
    ];

    public function provides(): array
    {
        return [EmployeeScheduleService::class];
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
