<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\Implement\AuthImplement;
use Illuminate\Support\ServiceProvider;

class AuthProvider extends ServiceProvider
{
    public array $singletons = [
        AuthService::class => AuthImplement::class
    ];

    public function provides(): array
    {
        return [AuthService::class];
    }

    /**
     * Register services.
     *
     * @return void
     */
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
