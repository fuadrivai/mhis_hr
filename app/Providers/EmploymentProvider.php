<?php

namespace App\Providers;

use App\Services\EmploymentService;
use App\Services\Implement\EmploymentImplement;
use Illuminate\Support\ServiceProvider;

class EmploymentProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
     public array $singletons = [
        EmploymentService::class => EmploymentImplement::class
    ];

    public function provides(): array
    {
        return [EmploymentService::class];
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
