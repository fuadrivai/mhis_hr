<?php

namespace App\Providers;

use App\Services\FamilyService;
use App\Services\Implement\FamilyImplement;
use Illuminate\Support\ServiceProvider;

class FamilyProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        FamilyService::class => FamilyImplement::class
    ];
    public function provides(): array
    {
        return [FamilyService::class];
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
