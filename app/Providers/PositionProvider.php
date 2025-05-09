<?php

namespace App\Providers;

use App\Services\Implement\PositionImplement;
use App\Services\PositionService;
use Illuminate\Support\ServiceProvider;

class PositionProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public array $singletons = [
        PositionService::class => PositionImplement::class
    ];
    public function provides(): array
    {
        return [PositionService::class];
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
