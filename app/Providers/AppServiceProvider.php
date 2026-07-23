<?php

namespace App\Providers;

use App\Services\ApprovalRequestService;
use App\Services\Implement\ApprovalRequestImplement;
use App\Services\Implement\RoleImplement;
use App\Services\RoleService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Session\FileSessionHandler;
use Illuminate\Filesystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */

    public array $singletons = [
        ApprovalRequestService::class => ApprovalRequestImplement::class,
        RoleService::class => RoleImplement::class,
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

        // Override the default file session driver to force 0777 permissions on session files
        Session::extend('file', function ($app) {
            $files = $app->make(Filesystem::class);
            $path = $app['config']['session.files'];
            $minutes = $app['config']['session.lifetime'];

            return new class($files, $path, $minutes) extends FileSessionHandler {
                #[\ReturnTypeWillChange]
                public function write($sessionId, $data)
                {
                    $result = parent::write($sessionId, $data);
                    
                    // Force 777 permissions directly on the session file we just created/updated
                    $sessionPath = $this->path.'/'.$sessionId;
                    if ($this->files->exists($sessionPath)) {
@chmod($sessionPath, 0777);
                    }
                    
                    return $result;
                }
            };
        });
    }
}
