<?php

namespace App\Providers;

use App\Services\AcademicYearService;
use App\Services\ApprovalRequestService;
use App\Services\CandidateRegistrationService;
use App\Services\HolidayService;
use App\Services\JobOpeningService;
use App\Services\JobRequirementService;
use App\Services\RecruitmentMonitorService;
use App\Services\Implement\AcademicYearImplement;
use App\Services\Implement\ApprovalRequestImplement;
use App\Services\Implement\CandidateRegistrationImplement;
use App\Services\Implement\HolidayImplement;
use App\Services\Implement\JobOpeningImplement;
use App\Services\Implement\JobRequirementImplement;
use App\Services\Implement\RecruitmentMonitorImplement;
use App\Services\Implement\LeaveAllocationImplement;
use App\Services\Implement\RoleImplement;
use App\Services\Implement\SopImplement;
use App\Services\LeaveAllocationService;
use App\Services\RoleService;
use App\Services\SopService;
use App\Services\RecruitmentAssessmentService;
use App\Services\RecruitmentInterviewService;
use App\Services\RecruitmentOfferService;
use App\Services\RecruitmentOnboardingService;
use App\Services\RecruitmentReportService;
use App\Services\Implement\RecruitmentAssessmentImplement;
use App\Services\Implement\RecruitmentInterviewImplement;
use App\Services\Implement\RecruitmentOfferImplement;
use App\Services\Implement\RecruitmentOnboardingImplement;
use App\Services\Implement\RecruitmentReportImplement;
use App\Services\Implement\VisitorManagementImplement;
use App\Services\VisitorManagementService;
use App\Services\Recruitment\Ai\CvTextExtractor;
use App\Services\Recruitment\Ai\AiMatchingProvider;
use App\Services\Recruitment\Ai\CvMatchingService;
use App\Services\Recruitment\Ai\Implement\DefaultCvTextExtractor;
use App\Services\Recruitment\Ai\Implement\OpenAiMatchingProvider;
use App\Services\Recruitment\Ai\Implement\CvMatchingImplement;
use Illuminate\Support\Facades\Gate;
use App\Models\Offer;
use App\Policies\OfferPolicy;
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
        JobOpeningService::class => JobOpeningImplement::class,
        JobRequirementService::class => JobRequirementImplement::class,
        RecruitmentMonitorService::class => RecruitmentMonitorImplement::class,
        AcademicYearService::class => AcademicYearImplement::class,
        RoleService::class => RoleImplement::class,
        LeaveAllocationService::class => LeaveAllocationImplement::class,
        HolidayService::class => HolidayImplement::class,
        SopService::class => SopImplement::class,
        CvTextExtractor::class => DefaultCvTextExtractor::class,
        AiMatchingProvider::class => OpenAiMatchingProvider::class,
        CvMatchingService::class => CvMatchingImplement::class,
        RecruitmentAssessmentService::class => RecruitmentAssessmentImplement::class,
        RecruitmentInterviewService::class => RecruitmentInterviewImplement::class,
        RecruitmentOfferService::class => RecruitmentOfferImplement::class,
        RecruitmentOnboardingService::class => RecruitmentOnboardingImplement::class,
        RecruitmentReportService::class => RecruitmentReportImplement::class,
        VisitorManagementService::class => VisitorManagementImplement::class,
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

        Gate::policy(Offer::class, OfferPolicy::class);
        Gate::define('viewCompensation', [OfferPolicy::class, 'viewCompensation']);

        Session::extend('secure_file', function ($app) {
            $path = $app['config']['session.files'];
            $lifetime = $app['config']['session.lifetime'];
            return new FileSessionHandler(new Filesystem, $path, $lifetime);
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
