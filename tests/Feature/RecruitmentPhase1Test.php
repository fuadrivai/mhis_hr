<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobOpening;
use App\Models\Personal;
use App\Models\Religion;
use App\Models\Role;
use App\Models\User;
use App\Models\ApplicationStageHistory;
use App\Services\CandidateRegistrationService;
use App\Services\JobOpeningService;
use App\Services\Implement\JobOpeningImplement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RecruitmentPhase1Test extends TestCase
{
    private ?User $admin = null;
    private array $cleanupOpeningIds = [];
    private array $cleanupCandidateIds = [];
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

    public function test_job_opening_service_is_bound(): void
    {
        $service = $this->app->make(JobOpeningService::class);
        $this->assertInstanceOf(JobOpeningImplement::class, $service);
    }

    public function test_opening_automatically_filled_when_headcount_reached(): void
    {
        $branch = Branch::first();
        $this->assertNotNull($branch);

        /** @var JobOpeningService $openingService */
        $openingService = $this->app->make(JobOpeningService::class);
        /** @var CandidateRegistrationService $candidateService */
        $candidateService = $this->app->make(CandidateRegistrationService::class);

        // 1. Create opening with headcount = 2
        $opening = $openingService->createOpening([
            'title'      => 'Test Headcount Vacancy ' . uniqid(),
            'branch_id'  => $branch->id,
            'headcount'  => 2,
            'opened_at'  => date('Y-m-d'),
            'status'     => 'open',
        ], $this->admin);
        $this->cleanupOpeningIds[] = $opening->id;

        $this->assertSame('open', $opening->status);
        $this->assertSame(0, $opening->filled_count);

        // 2. Candidate 1 registers and advances to joined
        $app1 = $candidateService->registerCandidate([
            'full_name'      => 'Candidate One ' . uniqid(),
            'email'          => 'cand1_' . uniqid() . '@example.com',
            'phone'          => '08123456781',
            'job_opening_id' => $opening->id,
            'source'         => 'LinkedIn',
            'consent'        => '1',
        ], 'hr', $this->admin);
        $this->cleanupCandidateIds[] = $app1->candidate_id;

        $candidateService->updateCandidateApplication(
            $app1->candidate,
            $app1,
            ['stage' => 'screening'],
            $this->admin
        );
        $candidateService->updateCandidateApplication(
            $app1->candidate,
            $app1,
            ['stage' => 'joined'],
            $this->admin
        );

        $opening->refresh();
        $this->assertSame(1, $opening->filled_count);
        $this->assertSame('open', $opening->status);

        // 3. Candidate 2 registers and advances to joined
        $app2 = $candidateService->registerCandidate([
            'full_name'      => 'Candidate Two ' . uniqid(),
            'email'          => 'cand2_' . uniqid() . '@example.com',
            'phone'          => '08123456782',
            'job_opening_id' => $opening->id,
            'source'         => 'LinkedIn',
            'consent'        => '1',
        ], 'hr', $this->admin);
        $this->cleanupCandidateIds[] = $app2->candidate_id;

        $candidateService->updateCandidateApplication(
            $app2->candidate,
            $app2,
            ['stage' => 'joined'],
            $this->admin
        );

        // 4. Headcount reached -> status automatically filled!
        $opening->refresh();
        $this->assertSame(2, $opening->filled_count);
        $this->assertSame('filled', $opening->status);

        // 5. Candidate 2 moves back from joined with reason -> status reopens!
        $candidateService->updateCandidateApplication(
            $app2->candidate,
            $app2,
            ['stage' => 'offer', 'reason' => 'Candidate postponed joining'],
            $this->admin
        );

        $opening->refresh();
        $this->assertSame(1, $opening->filled_count);
        $this->assertSame('open', $opening->status);
    }

    public function test_self_registration_only_shows_open_openings(): void
    {
        $branch = Branch::first();
        $openingService = $this->app->make(JobOpeningService::class);

        $openTitle = 'Open Vacancy ' . uniqid();
        $draftTitle = 'Draft Vacancy ' . uniqid();
        $closedTitle = 'Closed Vacancy ' . uniqid();

        $openOp = $openingService->createOpening([
            'title'     => $openTitle,
            'branch_id' => $branch->id,
            'headcount' => 1,
            'opened_at' => date('Y-m-d'),
            'status'    => 'open',
        ], $this->admin);
        $this->cleanupOpeningIds[] = $openOp->id;

        $draftOp = $openingService->createOpening([
            'title'     => $draftTitle,
            'branch_id' => $branch->id,
            'headcount' => 1,
            'opened_at' => date('Y-m-d'),
            'status'    => 'draft',
        ], $this->admin);
        $this->cleanupOpeningIds[] = $draftOp->id;

        $closedOp = $openingService->createOpening([
            'title'     => $closedTitle,
            'branch_id' => $branch->id,
            'headcount' => 1,
            'opened_at' => date('Y-m-d'),
            'status'    => 'open',
        ], $this->admin);
        $this->cleanupOpeningIds[] = $closedOp->id;
        $openingService->closeOpening($closedOp, 'Closed for test', $this->admin);

        $response = $this->get('/careers/candidate-registration');
        $response->assertOk();
        $response->assertSee($openTitle);
        $response->assertDontSee($draftTitle);
        $response->assertDontSee($closedTitle);
    }

    public function test_stage_history_timeline_and_transition_guard(): void
    {
        $branch = Branch::first();
        /** @var JobOpeningService $openingService */
        $openingService = $this->app->make(JobOpeningService::class);
        /** @var CandidateRegistrationService $candidateService */
        $candidateService = $this->app->make(CandidateRegistrationService::class);

        $opening = $openingService->createOpening([
            'title'     => 'Timeline Test Opening ' . uniqid(),
            'branch_id' => $branch->id,
            'headcount' => 3,
            'opened_at' => date('Y-m-d'),
            'status'    => 'open',
        ], $this->admin);
        $this->cleanupOpeningIds[] = $opening->id;

        $app = $candidateService->registerCandidate([
            'full_name'      => 'Timeline Candidate ' . uniqid(),
            'email'          => 'timeline_' . uniqid() . '@example.com',
            'phone'          => '08123456789',
            'job_opening_id' => $opening->id,
            'source'         => 'Referral',
            'consent'        => '1',
        ], 'hr', $this->admin);
        $this->cleanupCandidateIds[] = $app->candidate_id;

        // Initial history exists
        $initialHistory = ApplicationStageHistory::where('candidate_application_id', $app->id)->first();
        $this->assertNotNull($initialHistory);
        $this->assertSame('applied', $initialHistory->to_stage);

        // Advance: applied -> screening
        $candidateService->updateCandidateApplication(
            $app->candidate,
            $app,
            ['stage' => 'screening'],
            $this->admin
        );

        // Attempt reject without reason -> must fail
        $failedReject = false;
        try {
            $candidateService->updateCandidateApplication(
                $app->candidate,
                $app,
                ['stage' => 'rejected'],
                $this->admin
            );
        } catch (ValidationException $e) {
            $failedReject = true;
        }
        $this->assertTrue($failedReject, 'Reject without reason must throw ValidationException');

        // Reject with reason -> succeeds
        $candidateService->updateCandidateApplication(
            $app->candidate,
            $app,
            ['stage' => 'rejected', 'reason' => 'Qualifications did not match requirements.'],
            $this->admin
        );

        $app->refresh();
        $this->assertSame('rejected', $app->stage);
        $this->assertSame('rejected', $app->status);

        // Verify history entries
        $histories = ApplicationStageHistory::where('candidate_application_id', $app->id)
            ->orderBy('id', 'asc')
            ->get();

        $this->assertCount(3, $histories);
        $this->assertSame('Qualifications did not match requirements.', $histories[2]->reason);
        $this->assertSame('rejected', $histories[2]->to_stage);
        $this->assertSame('screening', $histories[2]->from_stage);
    }

    public function test_branch_scoping_for_scoped_user(): void
    {
        $branches = Branch::where('is_active', true)->take(2)->get();
        if ($branches->count() < 2) {
            $this->markTestSkipped('Need at least 2 branches to test branch scoping.');
        }

        $branchA = $branches[0];
        $branchB = $branches[1];

        $religionId = Religion::first()?->id ?? 1;

        // Create a scoped user assigned to Branch A
        $userA = User::create([
            'name'     => 'Branch A Recruiter',
            'email'    => 'recruiter_a_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->cleanupUserIds[] = $userA->id;
        $userA->roles()->attach(3); // HR role

        $personal = Personal::create([
            'first_name'  => 'Recruiter',
            'last_name'   => 'A',
            'fullname'    => 'Recruiter A',
            'email'       => $userA->email,
            'religion_id' => $religionId,
        ]);
        $employment = Employment::create([
            'branch_id' => $branchA->id,
            'status'    => true,
        ]);
        $employee = Employee::create([
            'user_id'       => $userA->id,
            'personal_id'   => $personal->id,
            'employment_id' => $employment->id,
            'is_active'     => true,
        ]);

        /** @var JobOpeningService $openingService */
        $openingService = $this->app->make(JobOpeningService::class);

        // Create Opening in Branch A and Opening in Branch B
        $openingA = $openingService->createOpening([
            'title'     => 'Opening Branch A ' . uniqid(),
            'branch_id' => $branchA->id,
            'headcount' => 1,
            'opened_at' => date('Y-m-d'),
            'status'    => 'open',
        ], $this->admin);
        $this->cleanupOpeningIds[] = $openingA->id;

        $openingB = $openingService->createOpening([
            'title'     => 'Opening Branch B ' . uniqid(),
            'branch_id' => $branchB->id,
            'headcount' => 1,
            'opened_at' => date('Y-m-d'),
            'status'    => 'open',
        ], $this->admin);
        $this->cleanupOpeningIds[] = $openingB->id;

        // User A list openings -> only sees Branch A
        $userAList = $openingService->getOpenings([], $userA);
        $this->assertTrue($userAList->contains('id', $openingA->id));
        $this->assertFalse($userAList->contains('id', $openingB->id));

        // User A details on Branch A -> ok
        $detailsA = $openingService->getOpeningDetails($openingA, $userA);
        $this->assertSame($openingA->id, $detailsA['opening']->id);

        // User A details on Branch B -> AuthorizationException!
        $denied = false;
        try {
            $openingService->getOpeningDetails($openingB, $userA);
        } catch (AuthorizationException $e) {
            $denied = true;
        }
        $this->assertTrue($denied, 'Scoped user should be denied access to opening of another branch');
    }
}
