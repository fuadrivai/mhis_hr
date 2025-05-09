<?php

namespace App\Providers;

use App\Services\AnnouncementService;
use App\Services\Implement\AnnouncementImplement;
use Illuminate\Support\ServiceProvider;

class AnnouncementProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        AnnouncementService::class => AnnouncementImplement::class
    ];
    public function provides(): array
    {
        return [AnnouncementService::class];
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
