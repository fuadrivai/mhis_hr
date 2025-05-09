<?php

namespace App\Providers;

use App\Services\EmployeeService;
use App\Services\Implement\EmployeeImplement;
use Illuminate\Support\ServiceProvider;

class EmployeeProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public array $singletons = [
        EmployeeService::class => EmployeeImplement::class
    ];
    public function provides(): array
    {
        return [EmployeeService::class];
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
