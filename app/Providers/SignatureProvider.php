<?php

namespace App\Providers;

use App\Services\Implement\SignatureImplement;
use App\Services\SignatureService;
use Illuminate\Support\ServiceProvider;

class SignatureProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        SignatureService::class => SignatureImplement::class
    ];
    public function register()
    {
        return [SignatureService::class];
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
