<?php

namespace App\Providers;

use App\Services\Implement\TimeOffImplement;
use App\Services\TimeOffService;
use Illuminate\Support\ServiceProvider;

class TimeOffProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        TimeOffService::class => TimeOffImplement::class
    ];
    public function register()
    {
        return [TimeOffService::class];
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
