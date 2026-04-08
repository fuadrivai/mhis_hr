<?php

namespace App\Providers;

use App\Services\ApprovalRequestService;
use App\Services\Implement\ApprovalRequestImplement;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */

    public array $singletons = [
        ApprovalRequestService::class => ApprovalRequestImplement::class,
    ];

    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
    }
}
