<?php

namespace App\Providers;

use App\Services\EmergencyContactService;
use App\Services\Implement\EmergencyContactImplement;
use Illuminate\Support\ServiceProvider;

class EmergencyContactProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        EmergencyContactService::class => EmergencyContactImplement::class
    ];
    public function provides(): array
    {
        return [EmergencyContactService::class];
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
