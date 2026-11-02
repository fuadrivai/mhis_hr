<?php

namespace Tests\Feature;

use App\Models\ApplicationOnboarding;
use App\Models\ApplicationRejection;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\Employee;
use App\Models\Employment;
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
use App\Services\RecruitmentOnboardingService;
use App\Services\Implement\RecruitmentOfferImplement;
use App\Services\Implement\RecruitmentOnboardingImplement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RecruitmentPhase4Test extends TestCase
{
    private ?User $admin = null;
    private array $cleanupCandidateIds = [];
    private array $cleanupOpeningIds = [];
    private array $cleanupUserIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
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

    private function createTestUserWithEmployee(string $name, int $roleId = 3): array
    {
        $branch = Branch::first();
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

    private function createTestApplication(int $headcount = 2): CandidateApplication
    {
        $branch = Branch::first();
        /** @var JobOpeningService $openingService */
        $openingService = $this->app->make(JobOpeningService::class);
        /** @var CandidateRegistrationService $candidateService */
        $candidateService = $this->app->make(CandidateRegistrationService::class);

        $opening = $openingService->createOpening([
            'title'     => 'Phase 4 Opening ' . uniqid(),
            'branch_id' => $branch->id,
            'headcount' => $headcount,
            'opened_at' => date('Y-m-d'),
            'status'    => 'open',
        ], $this->admin);
        $this->cleanupOpeningIds[] = $opening->id;

        $app = $candidateService->registerCandidate([
            'full_name'      => 'Phase 4 Candidate ' . uniqid(),
            'email'          => 'p4_' . uniqid() . '@example.com',
            'phone'          => '08123456789',
            'current_city'   => 'South Tangerang',
            'job_opening_id' => $opening->id,
            'source'         => 'LinkedIn',
            'consent'        => '1',
        ], 'hr', $this->admin);
        $this->cleanupCandidateIds[] = $app->candidate_id;

        return $app;
    }

    public function test_phase4_services_are_bound_to_implementations(): void
    {
        $offerService = $this->app->make(RecruitmentOfferService::class);
        $onboardingService = $this->app->make(RecruitmentOnboardingService::class);

        $this->assertInstanceOf(RecruitmentOfferImplement::class, $offerService);
        $this->assertInstanceOf(RecruitmentOnboardingImplement::class, $onboardingService);
    }

    public function test_rejection_requires_reason_and_reopen_requires_reason(): void
    {
        /** @var CandidateRegistrationService $candidateService */
        $candidateService = $this->app->make(CandidateRegistrationService::class);
        $application = $this->createTestApplication();

        // 1. Moving to rejected without reason fails validation
        $this->expectException(ValidationException::class);
        $candidateService->updateCandidateApplication(
            $application->candidate,
            $application,
            [
                'stage'  => 'rejected',
                'reason' => '',
            ],
            $this->admin
        );
    }

    public function test_rejection_records_application_rejection_and_history(): void
    {
        /** @var CandidateRegistrationService $candidateService */
        $candidateService = $this->app->make(CandidateRegistrationService::class);
        $application = $this->createTestApplication();
        $reason = RejectionReason::firstOrCreate(['name' => 'Skill mismatch'], ['is_active' => true, 'sort_order' => 1]);

        $updated = $candidateService->updateCandidateApplication(
            $application->candidate,
            $application,
            [
                'stage'               => 'rejected',
                'rejection_reason_id' => $reason->id,
                'rejection_notes'     => 'Candidate lacking required Laravel experience',
                'reason'              => 'Candidate lacking required Laravel experience',
            ],
            $this->admin
        );

        $this->assertEquals('rejected', $updated->stage);
        $this->assertDatabaseHas('application_rejections', [
            'candidate_application_id' => $application->id,
            'rejection_reason_id'      => $reason->id,
            'notes'                    => 'Candidate lacking required Laravel experience',
            'rejected_by'              => $this->admin->id,
        ]);

        // Reopening without reason fails
        try {
            $candidateService->updateCandidateApplication(
                $application->candidate,
                $application,
                [
                    'stage'  => 'screening',
                    'reason' => '',
                ],
                $this->admin
            );
            $this->fail('Reopening without reason should fail.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
        }

        // Reopening with reason succeeds
        $reopened = $candidateService->updateCandidateApplication(
            $application->candidate,
            $application,
            [
                'stage'  => 'screening',
                'reason' => 'Candidate updated portfolio with strong Laravel projects',
            ],
            $this->admin
        );
        $this->assertEquals('screening', $reopened->stage);
    }

    public function test_two_level_sequential_approval_workflow(): void
    {
        /** @var RecruitmentOfferService $offerService */
        $offerService = $this->app->make(RecruitmentOfferService::class);
        $application = $this->createTestApplication();

        [$userA, $empA] = $this->createTestUserWithEmployee('Approver Alpha', 3);
        [$userB, $empB] = $this->createTestUserWithEmployee('Approver Beta', 3);

        $offer = $offerService->createOffer($application, [
            'position_title'      => 'Senior Software Engineer',
            'basic_salary'        => '15000000',
            'allowances'          => [['name' => 'Transport', 'amount' => 1000000]],
            'submit_for_approval' => true,
            'approvers'           => [
                ['level' => 1, 'employee_id' => $empA->id],
                ['level' => 2, 'employee_id' => $empB->id],
            ],
        ], $this->admin);

        $this->assertEquals('pending_approval', $offer->status);
        $this->assertEquals(1, $offer->currentActiveLevel());

        // 1. Level 2 approver (Emp B) cannot approve while Level 1 is pending
        try {
            $offerService->approveStep($offer, $empB->id, 'Premature approval', $userB);
            $this->fail('Level 2 approver should not be able to act before Level 1 completes.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('step', $e->errors());
        }

        // 2. Level 1 approver (Emp A) approves
        $offer = $offerService->approveStep($offer, $empA->id, 'Level 1 approved by Alpha', $userA);
        $this->assertEquals('pending_approval', $offer->status);
        $this->assertEquals(2, $offer->currentActiveLevel());

        // 3. Level 2 approver (Emp B) now approves
        $offer = $offerService->approveStep($offer, $empB->id, 'Final approval by Beta', $userB);
        $this->assertEquals('approved', $offer->status);
        $this->assertTrue($offer->areAllStepsApproved());
    }

    public function test_approver_rejection_reverts_offer_to_draft(): void
    {
        /** @var RecruitmentOfferService $offerService */
        $offerService = $this->app->make(RecruitmentOfferService::class);
        $application = $this->createTestApplication();

        [$userA, $empA] = $this->createTestUserWithEmployee('Approver Alpha', 3);

        $offer = $offerService->createOffer($application, [
            'position_title'      => 'Math Teacher',
            'basic_salary'        => '8000000',
            'submit_for_approval' => true,
            'approvers'           => [
                ['level' => 1, 'employee_id' => $empA->id],
            ],
        ], $this->admin);

        // Rejection requires comment
        try {
            $offerService->rejectStep($offer, $empA->id, '', $userA);
            $this->fail('Rejection without comment should fail.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('comment', $e->errors());
        }

        // Rejection with comment reverts to draft
        $offer = $offerService->rejectStep($offer, $empA->id, 'Salary exceeds budget for Math Teacher', $userA);
        $this->assertEquals('draft', $offer->status);

        // Resubmitting after revising creates version 2
        $offerV2 = $offerService->createOffer($application, [
            'position_title'      => 'Math Teacher',
            'basic_salary'        => '7500000',
            'submit_for_approval' => true,
            'approvers'           => [
                ['level' => 1, 'employee_id' => $empA->id],
            ],
        ], $this->admin);

        $this->assertEquals(2, $offerV2->version);
        $this->assertEquals('pending_approval', $offerV2->status);
    }

    public function test_creator_cannot_self_approve_and_duplicate_approvers_blocked(): void
    {
        /** @var RecruitmentOfferService $offerService */
        $offerService = $this->app->make(RecruitmentOfferService::class);
        $application = $this->createTestApplication();

        // Admin who creates the offer has an employee profile
        [$adminUser, $adminEmp] = $this->createTestUserWithEmployee('Admin HR Head', 1);

        // 1. Assigning creator as approver fails validation
        try {
            $offerService->createOffer($application, [
                'position_title' => 'Science Teacher',
                'basic_salary'   => '7000000',
                'approvers'      => [
                    ['level' => 1, 'employee_id' => $adminEmp->id],
                ],
            ], $adminUser);
            $this->fail('Creator should not be allowed as approver.');
        } catch (ValidationException $e) {
            $this->assertTrue(isset($e->errors()['approvers.0.employee_id']));
        }

        // 2. Adding same employee twice fails validation
        [$userA, $empA] = $this->createTestUserWithEmployee('Approver Alpha', 3);
        try {
            $offerService->createOffer($application, [
                'position_title' => 'Science Teacher',
                'basic_salary'   => '7000000',
                'approvers'      => [
                    ['level' => 1, 'employee_id' => $empA->id],
                    ['level' => 2, 'employee_id' => $empA->id],
                ],
            ], $this->admin);
            $this->fail('Duplicate approvers on the same offer should be rejected.');
        } catch (ValidationException $e) {
            $this->assertTrue(isset($e->errors()['approvers.1.employee_id']));
        }
    }

    public function test_strict_compensation_access_control_matrix(): void
    {
        /** @var RecruitmentOfferService $offerService */
        $offerService = $this->app->make(RecruitmentOfferService::class);
        $application = $this->createTestApplication();

        [$approverUser, $approverEmp] = $this->createTestUserWithEmployee('Assigned Approver', 3);
        [$otherUser, $otherEmp] = $this->createTestUserWithEmployee('Regular HR Non-Monitor', 3);
        [$monitorUser, $monitorEmp] = $this->createTestUserWithEmployee('Recruitment Monitor User', 3);

        // Make monitorUser a recruitment monitor
        RecruitmentMonitor::create(['employee_id' => $monitorEmp->id]);

        $offer = $offerService->createOffer($application, [
            'position_title' => 'Executive Director',
            'basic_salary'   => '25000000',
            'allowances'     => [['name' => 'Housing', 'amount' => 5000000]],
            'approvers'      => [
                ['level' => 1, 'employee_id' => $approverEmp->id],
            ],
        ], $this->admin);

        // 1. Administrator CAN view
        $this->assertTrue($offer->canViewCompensation($this->admin));
        $this->assertEquals('Rp 25.000.000', $offer->getFormattedBasicSalaryFor($this->admin));

        // 2. Recruitment Monitor CAN view
        $this->assertTrue($offer->canViewCompensation($monitorUser));
        $this->assertEquals('Rp 25.000.000', $offer->getFormattedBasicSalaryFor($monitorUser));

        // 3. Assigned Approver CAN view
        $this->assertTrue($offer->canViewCompensation($approverUser));
        $this->assertEquals('Rp 25.000.000', $offer->getFormattedBasicSalaryFor($approverUser));

        // 4. Non-monitor, non-approver user CANNOT view (Restricted)
        $this->assertFalse($offer->canViewCompensation($otherUser));
        $this->assertEquals('[Restricted]', $offer->getFormattedBasicSalaryFor($otherUser));
        $this->assertEquals(['status' => '[Restricted]'], $offer->getFormattedAllowancesFor($otherUser));

        // 5. JSON serialization masking test
        $this->actingAs($otherUser);
        $serialized = $offer->toArray();
        $this->assertEquals('[Restricted]', $serialized['basic_salary']);
        $this->assertEquals('[Restricted]', $serialized['allowances']);
    }

    public function test_contract_signing_and_joined_stage_gates(): void
    {
        /** @var CandidateRegistrationService $candidateService */
        $candidateService = $this->app->make(CandidateRegistrationService::class);
        /** @var RecruitmentOfferService $offerService */
        $offerService = $this->app->make(RecruitmentOfferService::class);
        /** @var RecruitmentOnboardingService $onboardingService */
        $onboardingService = $this->app->make(RecruitmentOnboardingService::class);

        $application = $this->createTestApplication(headcount: 1);

        // 1. Moving to contract_signing without accepted offer is blocked
        try {
            $candidateService->updateCandidateApplication(
                $application->candidate,
                $application,
                ['stage' => 'contract_signing', 'reason' => ''],
                $this->admin
            );
            $this->fail('Moving to contract_signing without accepted offer should be blocked.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('stage', $e->errors());
        }

        // Accept an offer
        [$userA, $empA] = $this->createTestUserWithEmployee('Approver A', 3);
        $offer = $offerService->createOffer($application, [
            'position_title'      => 'Counselor',
            'basic_salary'        => '9000000',
            'submit_for_approval' => true,
            'approvers'           => [['level' => 1, 'employee_id' => $empA->id]],
        ], $this->admin);
        $offerService->approveStep($offer, $empA->id, 'Approved', $userA);
        $offerService->markAsSent($offer, ['sent_at' => now()], $this->admin);
        $offerService->recordCandidateResponse($offer, 'accepted', null, $this->admin);

        // 2. Now advancing to contract_signing succeeds
        $candidateService->updateCandidateApplication(
            $application->candidate,
            $application,
            ['stage' => 'contract_signing'],
            $this->admin
        );
        $this->assertEquals('contract_signing', $application->refresh()->stage);

        // 3. Advancing to joined without signed contract is blocked
        try {
            $candidateService->updateCandidateApplication(
                $application->candidate,
                $application,
                ['stage' => 'joined', 'reason' => ''],
                $this->admin
            );
            $this->fail('Moving to joined without signed contract should be blocked.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('stage', $e->errors());
        }

        // 4. Sign contract and record join date
        $fakePdf = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');
        $onboardingService->updateContract($application, [
            'contract_status' => 'signed',
        ], $fakePdf, $this->admin);

        $onboardingService->updateJoinStatus($application, [
            'join_status' => 'joined',
            'join_date'   => date('Y-m-d'),
            'reason'      => 'First day completed orientation',
        ], $this->admin);

        $application->refresh();
        $this->assertEquals('joined', $application->stage);

        // 5. Headcount fill check: opening headcount was 1, so opening is now automatically filled!
        $opening = JobOpening::find($application->job_opening_id);
        $this->assertEquals('filled', $opening->status);
    }

    public function test_create_employee_draft_is_idempotent_and_does_not_copy_salary(): void
    {
        /** @var RecruitmentOfferService $offerService */
        $offerService = $this->app->make(RecruitmentOfferService::class);
        /** @var RecruitmentOnboardingService $onboardingService */
        $onboardingService = $this->app->make(RecruitmentOnboardingService::class);

        $application = $this->createTestApplication();

        // Setup candidate with accepted offer, signed contract, and joined
        [$userA, $empA] = $this->createTestUserWithEmployee('Approver Alpha', 3);
        $offer = $offerService->createOffer($application, [
            'position_title'      => 'Art Teacher',
            'basic_salary'        => '8500000',
            'submit_for_approval' => true,
            'approvers'           => [['level' => 1, 'employee_id' => $empA->id]],
        ], $this->admin);
        $offerService->approveStep($offer, $empA->id, 'Approved', $userA);
        $offerService->markAsSent($offer, ['sent_at' => now()], $this->admin);
        $offerService->recordCandidateResponse($offer, 'accepted', null, $this->admin);

        $fakePdf = UploadedFile::fake()->create('signed_contract.pdf', 300, 'application/pdf');
        $onboardingService->updateContract($application, [
            'contract_status' => 'signed',
        ], $fakePdf, $this->admin);

        $onboardingService->updateJoinStatus($application, [
            'join_status' => 'joined',
            'join_date'   => date('Y-m-d'),
            'reason'      => 'Candidate joined',
        ], $this->admin);

        // 1. Create Employee Draft
        $employee = $onboardingService->createEmployeeDraft($application, $this->admin);

        $this->assertNotNull($employee->id);
        $this->assertEquals(0, $employee->is_active);
        $this->assertEquals('draft', $employee->employee_status);
        $this->assertNull($employee->user_id);
        $this->assertNull($employee->payrol_info_id); // Confidential salary not copied to core employee

        // Check personal and employment details
        $this->assertEquals($application->candidate->full_name, $employee->personal->fullname);
        $this->assertEquals($application->candidate->email, $employee->personal->email);
        $this->assertEquals(2, $employee->personal->religion_id); // Defaulted to Others (id 2)
        $this->assertEquals('EMP-DRAFT-' . $application->candidate_id, $employee->employment->employee_id);

        // 2. Calling createEmployeeDraft again returns the exact same employee (idempotent)
        $employeeSecondCall = $onboardingService->createEmployeeDraft($application, $this->admin);
        $this->assertEquals($employee->id, $employeeSecondCall->id);
        $this->assertEquals(1, Employee::where('personal_id', $employee->personal_id)->count());
    }

    public function test_rejection_reasons_settings_crud(): void
    {
        $this->actingAs($this->admin);

        // 1. Index
        $response = $this->get(route('recruitment.settings.rejection-reasons.index'));
        $response->assertStatus(200);
        $response->assertSee('Master Rejection Reasons');

        // 2. Store
        $reasonName = 'Custom Reason ' . uniqid();
        $storeResponse = $this->post(route('recruitment.settings.rejection-reasons.store'), [
            'name'       => $reasonName,
            'sort_order' => 10,
            'is_active'  => 1,
        ]);
        $storeResponse->assertRedirect(route('recruitment.settings.rejection-reasons.index'));
        $this->assertDatabaseHas('rejection_reasons', ['name' => $reasonName]);

        $reason = RejectionReason::where('name', $reasonName)->first();

        // 3. Update
        $this->put(route('recruitment.settings.rejection-reasons.update', $reason), [
            'name'       => $reasonName . ' Updated',
            'sort_order' => 12,
            'is_active'  => 1,
        ]);
        $this->assertDatabaseHas('rejection_reasons', ['name' => $reasonName . ' Updated']);

        // 4. Toggle Active
        $this->patch(route('recruitment.settings.rejection-reasons.toggle', $reason));
        $this->assertFalse($reason->refresh()->is_active);

        // 5. Delete
        $this->delete(route('recruitment.settings.rejection-reasons.destroy', $reason));
        $this->assertDatabaseMissing('rejection_reasons', ['id' => $reason->id]);
    }
}

