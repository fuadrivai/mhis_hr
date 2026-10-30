<?php

namespace App\Providers;

use App\Services\AcademicYearService;
use App\Services\ApprovalRequestService;
use App\Services\CandidateRegistrationService;
use App\Services\HolidayService;
use App\Services\JobRequirementService;
use App\Services\RecruitmentMonitorService;
use App\Services\Implement\AcademicYearImplement;
use App\Services\Implement\ApprovalRequestImplement;
use App\Services\Implement\CandidateRegistrationImplement;
use App\Services\Implement\HolidayImplement;
use App\Services\Implement\JobRequirementImplement;
use App\Services\Implement\RecruitmentMonitorImplement;
use App\Services\Implement\LeaveAllocationImplement;
use App\Services\Implement\RoleImplement;
use App\Services\Implement\SopImplement;
use App\Services\LeaveAllocationService;
use App\Services\RoleService;
use App\Services\SopService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Session\FileSessionHandler;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */

    public array $singletons = [
        ApprovalRequestService::class => ApprovalRequestImplement::class,
        CandidateRegistrationService::class => CandidateRegistrationImplement::class,
        JobRequirementService::class => JobRequirementImplement::class,
        RecruitmentMonitorService::class => RecruitmentMonitorImplement::class,
        AcademicYearService::class => AcademicYearImplement::class,
        RoleService::class => RoleImplement::class,
        LeaveAllocationService::class => LeaveAllocationImplement::class,
        HolidayService::class => HolidayImplement::class,
        SopService::class => SopImplement::class,
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

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

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
