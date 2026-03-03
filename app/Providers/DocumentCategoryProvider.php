<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DocumentCategoryService;
use App\Services\DocumentCategoryImplement;

class DocumentCategoryProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */

    public array $singletons = [
        DocumentCategoryService::class => DocumentCategoryImplement::class
    ];
    public function provides(): array
    {
        return [DocumentCategoryService::class];
    }
    public function register(): void
    {
        // 
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
}
