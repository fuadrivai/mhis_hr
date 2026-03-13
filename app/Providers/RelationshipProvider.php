<?php

namespace App\Providers;

use App\Services\Implement\RelationshipImplement;
use App\Services\RelationshipService;
use Illuminate\Support\ServiceProvider;

class RelationshipProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        RelationshipService::class => RelationshipImplement::class
    ];
    public function provides(): array
    {
        return [RelationshipService::class];
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
