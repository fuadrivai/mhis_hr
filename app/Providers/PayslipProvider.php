<?php

namespace App\Providers;

use App\Services\Implement\PayslipImplement;
use App\Services\PayslipService;
use Illuminate\Support\ServiceProvider;

class PayslipProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        PayslipService::class => PayslipImplement::class
    ];
    public function register()
    {
        return [PayslipService::class];
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
