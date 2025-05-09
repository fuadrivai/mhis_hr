<?php

namespace App\Providers;

use App\Services\BranchService;
use App\Services\Implement\BranchImplement;
use Illuminate\Support\ServiceProvider;

class BranchProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public array $singletons = [
        BranchService::class => BranchImplement::class
    ];
    public function provides(): array
    {
        return [BranchService::class];
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
