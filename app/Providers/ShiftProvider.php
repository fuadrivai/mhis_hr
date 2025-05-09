<?php

namespace App\Providers;

use App\Services\Implement\ShiftImplement;
use App\Services\ShiftService;
use Illuminate\Support\ServiceProvider;

class ShiftProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        ShiftService::class => ShiftImplement::class
    ];
    public function register()
    {
        return [ShiftService::class];
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
