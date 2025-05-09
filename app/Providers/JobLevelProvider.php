<?php

namespace App\Providers;

use App\Services\Implement\JobLevelImplement;
use App\Services\JobLevelService;
use Illuminate\Support\ServiceProvider;

class JobLevelProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        JobLevelService::class => JobLevelImplement::class
    ];
    public function provides(): array
    {
        return [JobLevelService::class];
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
