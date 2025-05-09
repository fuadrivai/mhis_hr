<?php

namespace App\Providers;

use App\Services\GsheetLinkService;
use App\Services\Implement\GsheetLinkImplement;
use Illuminate\Support\ServiceProvider;

class GsheetLinkProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public array $singletons = [
        GsheetLinkService::class => GsheetLinkImplement::class
    ];
    public function register()
    {
        return [GsheetLinkService::class];
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
