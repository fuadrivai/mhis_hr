<?php

namespace App\Providers;

use App\Services\Implement\PersonImplement;
use App\Services\PersonService;
use Illuminate\Support\ServiceProvider;

class PersonProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public array $singletons = [
        PersonService::class => PersonImplement::class
    ];
    public function register()
    {
        return [PersonService::class];
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
