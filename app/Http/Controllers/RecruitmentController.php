<?php

namespace App\Http\Controllers;

use App\Services\RecruitmentReportService;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    private const SECTIONS = [
        'dashboard'    => 'Recruitment Dashboard',
        'candidates'   => 'Candidates',
        'applications' => 'Applications',
        'pipeline'     => 'Recruitment Pipeline',
        'interviews'   => 'Interview Schedule',
        'assessments'  => 'Assessments & Evaluations',
        'final-review' => 'Final Review',
        'talent-pool'  => 'Talent Pool',
        'reports'      => 'Recruitment Reports & Analytics',
        'settings'     => 'Recruitment Settings',
    ];

    public function __construct(
        protected RecruitmentReportService $reportService
    ) {}

    public function index(Request $request, $section = 'dashboard')
    {
        $user = auth()->user();

        if (!$user || !$user->canAccessRecruitment()) {
            abort(403, 'Unauthorized access to Recruitment module.');
        }

        abort_unless(isset(self::SECTIONS[$section]), 404);

        if ($section === 'candidates') {
            return redirect()->route('recruitment.candidates');
        }

        $filters = $request->only(['branch_id', 'job_opening_id', 'start_date', 'end_date', 'refresh', 'active_tab']);

        if ($section === 'dashboard') {
            $summary = $this->reportService->getDashboardSummary($user, $filters);
            $funnel = $this->reportService->getFunnelData($user, $filters);
            $actionRequired = $this->reportService->getActionRequiredItems($user, $filters);
            $filterOptions = $this->reportService->getFilterOptions($user);

            return view('recruitment.dashboard', [
                'title'          => self::SECTIONS['dashboard'],
                'summary'        => $summary,
                'funnel'         => $funnel,
                'actionRequired' => $actionRequired,
                'filterOptions'  => $filterOptions,
                'filters'        => $filters,
            ]);
        }

        if ($section === 'reports') {
            $filterOptions = $this->reportService->getFilterOptions($user);
            $funnel = $this->reportService->getFunnelData($user, $filters);
            $timeReport = $this->reportService->getTimeInStageAndTimeToHire($user, $filters);
            $sourceReport = $this->reportService->getSourceEffectiveness($user, $filters);
            $offerReport = $this->reportService->getOfferAcceptanceReport($user, $filters);
            $rejectionReport = $this->reportService->getRejectionDistribution($user, $filters);
            $headcountReport = $this->reportService->getHeadcountFillRateAndAging($user, $filters);
            $aiReport = $this->reportService->getAiScoreDistribution($user, $filters);

            return view('recruitment.reports', [
                'title'           => self::SECTIONS['reports'],
                'filters'         => $filters,
                'filterOptions'   => $filterOptions,
                'funnel'          => $funnel,
                'timeReport'      => $timeReport,
                'sourceReport'    => $sourceReport,
                'offerReport'     => $offerReport,
                'rejectionReport' => $rejectionReport,
                'headcountReport' => $headcountReport,
                'aiReport'        => $aiReport,
            ]);
        }

        return view('recruitment.index', [
            'title'   => self::SECTIONS[$section],
            'section' => $section,
        ]);
    }

    public function exportReport(Request $request, string $type)
    {
        $user = auth()->user();

        if (!$user || !$user->canAccessRecruitment()) {
            abort(403, 'Unauthorized access to Recruitment exports.');
        }

        $filters = $request->only(['branch_id', 'job_opening_id', 'start_date', 'end_date']);

        return $this->reportService->exportCsv($user, $type, $filters);
    }
}
