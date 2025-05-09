<?php

namespace App\Providers;

use App\Services\Implement\OrganizationImplement;
use App\Services\OrganizationService;
use Illuminate\Support\ServiceProvider;

class OrganizationProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        OrganizationService::class => OrganizationImplement::class
    ];
    public function provides(): array
    {
        return [OrganizationService::class];
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
