<?php

namespace App\Providers;

use App\Services\Implement\PersonalImplement;
use App\Services\PersonalService;
use Illuminate\Support\ServiceProvider;

class PersonalProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        PersonalService::class => PersonalImplement::class
    ];
    public function register()
    {
        return [PersonalService::class];
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
