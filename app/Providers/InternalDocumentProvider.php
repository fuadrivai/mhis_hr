<?php

namespace App\Providers;

use App\Services\Implement\InternalDocumentImplement;
use App\Services\InternalDocumentService;
use Illuminate\Support\ServiceProvider;

class InternalDocumentProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
   public array $singletons = [
        InternalDocumentService::class => InternalDocumentImplement::class
    ];
    public function register()
    {
        return [InternalDocumentService::class];
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
