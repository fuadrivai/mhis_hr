<?php

namespace App\Providers;

use App\Services\AnnouncementCategoryService;
use App\Services\Implement\AnnouncementCategoryImplement;
use Illuminate\Support\ServiceProvider;

class AnnouncementCategoryProvicder extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public array $singletons = [
        AnnouncementCategoryService::class => AnnouncementCategoryImplement::class
    ];
    public function provides(): array
    {
        return [AnnouncementCategoryService::class];
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
