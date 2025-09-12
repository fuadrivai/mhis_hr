<?php

namespace App\Providers;

use App\Services\Implement\LocationImplement;
use App\Services\LocationService;
use Illuminate\Support\ServiceProvider;

class LocationProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public array $singletons = [
        LocationService::class => LocationImplement::class
    ];
    public function register()
    {
        return [LocationService::class];
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
