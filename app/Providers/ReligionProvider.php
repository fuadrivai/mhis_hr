<?php

namespace App\Providers;

use App\Services\Implement\ReligionImplement;
use App\Services\ReligionService;
use Illuminate\Support\ServiceProvider;

class ReligionProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public array $singletons = [
        ReligionService::class => ReligionImplement::class
    ];
    public function provides(): array
    {
        return [ReligionService::class];
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
