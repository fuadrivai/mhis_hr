<?php

namespace App\Providers;

use App\Services\Implement\ScheduleImplement;
use App\Services\ScheduleService;
use Illuminate\Support\ServiceProvider;

class ScheduleProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        ScheduleService::class => ScheduleImplement::class
    ];
    public function register()
    {
        return [ScheduleService::class];
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
