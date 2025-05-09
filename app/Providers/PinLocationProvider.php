<?php

namespace App\Providers;

use App\Services\Implement\PinLocationImplement;
use App\Services\PinLocationService;
use Illuminate\Support\ServiceProvider;

class PinLocationProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        PinLocationService::class => PinLocationImplement::class
    ];
    public function register()
    {
        return [PinLocationService::class];
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
