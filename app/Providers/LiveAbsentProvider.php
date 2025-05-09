<?php

namespace App\Providers;

use App\Services\Implement\LiveAbsentImplement;
use App\Services\LiveAbsentService;
use Illuminate\Support\ServiceProvider;

class LiveAbsentProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        LiveAbsentService::class => LiveAbsentImplement::class
    ];
    public function provides(): array
    {
        return [LiveAbsentService::class];
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
