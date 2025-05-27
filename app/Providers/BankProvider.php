<?php

namespace App\Providers;

use App\Services\BankService;
use App\Services\Implement\BankImplement;
use Illuminate\Support\ServiceProvider;

class BankProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        BankService::class => BankImplement::class
    ];
    public function register()
    {
        return [BankService::class];
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
