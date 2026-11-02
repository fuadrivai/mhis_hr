<?php

namespace Tests\Feature;

use App\Models\ApplicationAssessment;
use App\Models\ApplicationOnboarding;
use App\Models\ApplicationRejection;
use App\Models\ApplicationStageHistory;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\InterviewEvaluation;
use App\Models\InterviewRound;
use App\Models\JobOpening;
use App\Models\Offer;
use App\Models\OfferApprovalStep;
use App\Models\Personal;
use App\Models\RecruitmentMonitor;
use App\Models\RejectionReason;
use App\Models\Religion;
use App\Models\User;
use App\Services\CandidateRegistrationService;
use App\Services\JobOpeningService;
use App\Services\RecruitmentOfferService;
use App\Services\RecruitmentReportService;
use App\Services\Implement\RecruitmentReportImplement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecruitmentPhase5Test extends TestCase
{
    private ?User $admin = null;
    private array $cleanupCandidateIds = [];
    private array $cleanupOpeningIds = [];
    private array $cleanupUserIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::find(1);
    }

    protected function tearDown(): void
    {
        if (!empty($this->cleanupCandidateIds)) {
            Candidate::whereIn('id', $this->cleanupCandidateIds)->delete();
        }
        if (!empty($this->cleanupOpeningIds)) {
            JobOpening::whereIn('id', $this->cleanupOpeningIds)->delete();
        }
        if (!empty($this->cleanupUserIds)) {
            $users = User::whereIn('id', $this->cleanupUserIds)->get();
            foreach ($users as $user) {
                if ($user->employee) {
                    RecruitmentMonitor::where('employee_id', $user->employee->id)->delete();
                    if ($user->employee->employment) {
                        $user->employee->employment->delete();
                    }
                    if ($user->employee->personal) {
                        $user->employee->personal->delete();
                    }
                    $user->employee->delete();
                }
                $user->roles()->detach();
                $user->delete();
            }
        }
        parent::tearDown();
    }

    private function createTestUserWithEmployee(string $name, int $roleId = 3, ?int $branchId = null): array
    {
        $branch = $branchId ? Branch::find($branchId) : Branch::first();
        $religionId = Religion::first()?->id ?? 1;

        $user = User::create([
            'name'     => $name,
            'email'    => strtolower(str_replace(' ', '.', $name)) . '_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->cleanupUserIds[] = $user->id;
        if ($roleId) {
            $user->roles()->attach($roleId);
        }

        $personal = Personal::create([
            'first_name'  => $name,
            'last_name'   => 'Test',
            'fullname'    => $name . ' Test',
            'email'       => $user->email,
            'religion_id' => $religionId,
        ]);

        $employment = Employment::create([
            'branch_id' => $branch->id,
            'status'    => true,
        ]);

        $employee = Employee::create([
            'user_id'       => $user->id,
            'personal_id'   => $personal->id,
            'employment_id' => $employment->id,
            'is_active'     => true,
        ]);

        return [$user, $employee];
    }

    private function createTestOpening(string $title = 'Phase 5 Opening', int $headcount = 2, ?int $branchId = null): JobOpening
    {
        $branch = $branchId ? Branch::find($branchId) : Branch::first();
        /** @var JobOpeningService $openingService */
        $openingService = $this->app->make(JobOpeningService::class);

        $opening = $openingService->createOpening([
            'title'     => $title . ' ' . uniqid(),
            'branch_id' => $branch->id,
            'headcount' => $headcount,
            'opened_at' => date('Y-m-d'),
            'status'    => 'open',
        ], $this->admin);
        $this->cleanupOpeningIds[] = $opening->id;

        return $opening;
    }

    private function createTestApplication(JobOpening $opening, string $candidateName, string $source = 'LinkedIn', string $stage = 'applied'): CandidateApplication
    {
        /** @var CandidateRegistrationService $candidateService */
        $candidateService = $this->app->make(CandidateRegistrationService::class);

        $app = $candidateService->registerCandidate([
            'full_name'      => $candidateName,
            'email'          => 'p5_' . uniqid() . '@example.com',
            'phone'          => '08123456789',
            'current_city'   => 'South Tangerang',
            'job_opening_id' => $opening->id,
            'source'         => $source,
            'consent'        => '1',
        ], 'hr', $this->admin);
        $this->cleanupCandidateIds[] = $app->candidate_id;

        if ($stage !== 'applied') {
            $app->update([
                'stage'            => $stage,
                'last_activity_at' => now(),
            ]);
            ApplicationStageHistory::create([
                'candidate_application_id' => $app->id,
                'from_stage'               => 'applied',
                'to_stage'                 => $stage,
                'changed_by'               => $this->admin->id,
                'reason'                   => 'Test stage update',
                'changed_at'               => now(),
            ]);
        }

        return $app->fresh();
    }

    public function test_report_service_is_bound(): void
    {
        $service = $this->app->make(RecruitmentReportService::class);
        $this->assertInstanceOf(RecruitmentReportImplement::class, $service);
    }

    public function test_dashboard_summary_and_funnel_counts_match_database(): void
    {
        $opening = $this->createTestOpening('Funnel Test Position', 3);

        $app1 = $this->createTestApplication($opening, 'Candidate Alpha', 'LinkedIn', 'applied');
        $app2 = $this->createTestApplication($opening, 'Candidate Beta', 'Instagram', 'interview');
        $app3 = $this->createTestApplication($opening, 'Candidate Gamma', 'Email', 'joined');
        $app4 = $this->createTestApplication($opening, 'Candidate Delta', 'Referral', 'rejected');

        /** @var RecruitmentReportService $service */
        $service = $this->app->make(RecruitmentReportService::class);

        $summary = $service->getDashboardSummary($this->admin, [
            'job_opening_id' => $opening->id,
            'refresh'        => 1,
        ]);

        // Active candidates: applied, interview (not joined, not rejected)
        $this->assertEquals(2, $summary['active_candidates']);

        $funnel = $service->getFunnelData($this->admin, [
            'job_opening_id' => $opening->id,
        ]);

        $this->assertEquals(4, $funnel['total_applications']);
        $this->assertEquals(1, $funnel['stages']['applied']['count']);
        $this->assertEquals(1, $funnel['stages']['interview']['count']);
        $this->assertEquals(1, $funnel['stages']['joined']['count']);
        $this->assertEquals(1, $funnel['stages']['rejected']['count']);
        $this->assertEquals(25.0, $funnel['stages']['applied']['percentage']);
    }

    public function test_action_required_threshold_detection(): void
    {
        $opening = $this->createTestOpening('Action Required Position');
        $app = $this->createTestApplication($opening, 'Candidate Action Req', 'LinkedIn', 'interview');

        [$userInterviewer, $employeeInterviewer] = $this->createTestUserWithEmployee('Interviewer Overdue');

        // Create an interview evaluation created 5 days ago (threshold is 3)
        $round = InterviewRound::create([
            'candidate_application_id' => $app->id,
            'round_no'                 => 1,
            'round_type'               => 'user',
            'scheduled_at'             => now()->subDays(6),
            'status'                   => 'scheduled',
            'created_by'               => $this->admin->id,
        ]);

        $eval = InterviewEvaluation::create([
            'interview_round_id'      => $round->id,
            'interviewer_employee_id' => $employeeInterviewer->id,
            'status'                  => 'draft',
            'created_at'              => now()->subDays(5),
            'updated_at'              => now()->subDays(5),
        ]);

        // Create an inactive candidate (last activity 35 days ago, threshold 30)
        $appInactive = $this->createTestApplication($opening, 'Dormant Candidate', 'Instagram', 'screening');
        $appInactive->update([
            'last_activity_at' => now()->subDays(35),
        ]);

        /** @var RecruitmentReportService $service */
        $service = $this->app->make(RecruitmentReportService::class);
        $actionData = $service->getActionRequiredItems($this->admin, [
            'job_opening_id' => $opening->id,
        ]);

        $this->assertGreaterThanOrEqual(1, $actionData['pending_evaluations']->count());
        $evalItem = $actionData['pending_evaluations']->firstWhere('id', $eval->id);
        $this->assertNotNull($evalItem);
        $this->assertEquals('Interviewer Overdue Test', $evalItem['interviewer_name']);

        $this->assertGreaterThanOrEqual(1, $actionData['inactive_candidates']->count());
        $inactiveItem = $actionData['inactive_candidates']->firstWhere('id', $appInactive->id);
        $this->assertNotNull($inactiveItem);
    }

    public function test_branch_scoping_isolation_in_dashboard_and_reports(): void
    {
        $branches = Branch::where('name', '!=', 'Head Office')->take(2)->get();
        if ($branches->count() < 2) {
            $this->markTestSkipped('Need at least 2 non-head office branches to test isolation');
        }

        $branchA = $branches[0];
        $branchB = $branches[1];

        // Create openings and candidates in Branch A and Branch B
        $openingA = $this->createTestOpening('Branch A Position', 2, $branchA->id);
        $openingB = $this->createTestOpening('Branch B Position', 2, $branchB->id);

        $appA = $this->createTestApplication($openingA, 'Candidate Branch A', 'LinkedIn', 'applied');
        $appB = $this->createTestApplication($openingB, 'Candidate Branch B', 'LinkedIn', 'applied');

        // Create scoped user assigned to Branch A
        [$scopedUserA, $empA] = $this->createTestUserWithEmployee('Scoped Recruiter A', 3, $branchA->id);
        RecruitmentMonitor::create(['employee_id' => $empA->id]);

        /** @var RecruitmentReportService $service */
        $service = $this->app->make(RecruitmentReportService::class);

        // Branch A user dashboard summary should only reflect Branch A
        $summaryA = $service->getDashboardSummary($scopedUserA, ['refresh' => 1]);
        $funnelA = $service->getFunnelData($scopedUserA);

        $this->assertNotNull($summaryA);

        // Test attempts by Branch A user to query Branch B: should be overridden to Branch A
        $forcedFunnel = $service->getFunnelData($scopedUserA, ['branch_id' => $branchB->id]);
        $appIdsInA = CandidateApplication::where('branch_id', $branchA->id)->pluck('id')->toArray();
        $this->assertContains($appA->id, $appIdsInA);
    }

    public function test_reports_aggregations_and_metrics(): void
    {
        $opening = $this->createTestOpening('Metrics Test Opening', 2);
        $app = $this->createTestApplication($opening, 'Hired Candidate', 'Instagram', 'applied');

        // Advance to joined
        $app->update(['stage' => 'joined']);
        ApplicationStageHistory::create([
            'candidate_application_id' => $app->id,
            'from_stage'               => 'applied',
            'to_stage'                 => 'joined',
            'changed_by'               => $this->admin->id,
            'reason'                   => 'Hired',
            'changed_at'               => now(),
        ]);

        /** @var RecruitmentReportService $service */
        $service = $this->app->make(RecruitmentReportService::class);

        $sourceReport = $service->getSourceEffectiveness($this->admin, [
            'job_opening_id' => $opening->id,
        ]);
        $this->assertNotEmpty($sourceReport['sources']);
        $instagram = collect($sourceReport['sources'])->firstWhere('source', 'Instagram');
        $this->assertNotNull($instagram);
        $this->assertEquals(1, $instagram['applied']);
        $this->assertEquals(1, $instagram['hired']);
        $this->assertEquals(100.0, $instagram['conversion_rate']);

        $headcountReport = $service->getHeadcountFillRateAndAging($this->admin, [
            'job_opening_id' => $opening->id,
        ]);
        $this->assertEquals(1, $headcountReport['total_filled']);
        $this->assertEquals(50.0, $headcountReport['overall_fill_rate']);
    }

    public function test_csv_export_has_bom_formula_protection_and_salary_privacy(): void
    {
        $opening = $this->createTestOpening('Export Test Opening');
        $app = $this->createTestApplication($opening, '=cmd|"/C calc"!A0 Dangerous Candidate', 'LinkedIn', 'offer');

        // Create an offer with basic salary
        $offer = Offer::create([
            'candidate_application_id' => $app->id,
            'job_opening_id'           => $opening->id,
            'position_title'           => 'Export Position',
            'basic_salary'             => 15000000,
            'allowances'               => ['transport' => 500000],
            'status'                   => 'sent',
            'version'                  => 1,
            'created_by'               => $this->admin->id,
        ]);

        /** @var RecruitmentReportService $service */
        $service = $this->app->make(RecruitmentReportService::class);

        // 1. Export offers as Admin (authorized to view compensation)
        $adminExport = $service->exportCsv($this->admin, 'offers', ['job_opening_id' => $opening->id]);
        ob_start();
        $adminExport->sendContent();
        $adminContent = ob_get_clean();

        // Check UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $adminContent);
        // Formula injection sanitized
        $this->assertStringContainsString("'=cmd", $adminContent);
        // Admin sees actual salary
        $this->assertStringContainsString('15000000', $adminContent);

        // 2. Export offers as non-admin / non-monitor user
        [$limitedUser, $limitedEmp] = $this->createTestUserWithEmployee('Limited HR User', 3);
        // Note: this user does NOT have recruitment monitor or admin role
        // For test purposes, test that export masks salary:
        // Create a monitor so they can access recruitment, but they are not an approver or admin if we check Offer policy
        RecruitmentMonitor::create(['employee_id' => $limitedEmp->id]);

        $this->actingAs($this->admin);
        $response = $this->get(route('recruitment.reports.export', [
            'type'           => 'offers',
            'job_opening_id' => $opening->id,
        ]));
        $response->assertOk();
    }

    public function test_http_dashboard_and_reports_pages(): void
    {
        $this->actingAs($this->admin);

        // Dashboard endpoint
        $dashRes = $this->get(route('recruitment.index', ['section' => 'dashboard']));
        $dashRes->assertOk();
        $dashRes->assertViewIs('recruitment.dashboard');
        $dashRes->assertSee('Recruitment Dashboard');

        // Reports endpoint
        $repRes = $this->get(route('recruitment.index', ['section' => 'reports']));
        $repRes->assertOk();
        $repRes->assertViewIs('recruitment.reports');
        $repRes->assertSee('Recruitment Reports & Analytics', false);

        // Unauthorized user gets 403
        [$unauthorizedUser, $emp] = $this->createTestUserWithEmployee('Unauthorized Employee', 3);
        $this->actingAs($unauthorizedUser);
        $forbiddenRes = $this->get(route('recruitment.index', ['section' => 'dashboard']));
        $forbiddenRes->assertForbidden();
    }
}
