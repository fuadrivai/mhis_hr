<?php

namespace Tests\Feature;

use App\Models\ApplicationAssessment;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\EvaluationCompetency;
use App\Models\InterviewEvaluation;
use App\Models\InterviewRound;
use App\Models\JobOpening;
use App\Models\Personal;
use App\Models\Religion;
use App\Models\User;
use App\Services\CandidateRegistrationService;
use App\Services\JobOpeningService;
use App\Services\RecruitmentAssessmentService;
use App\Services\RecruitmentInterviewService;
use App\Services\Implement\RecruitmentAssessmentImplement;
use App\Services\Implement\RecruitmentInterviewImplement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RecruitmentPhase3Test extends TestCase
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

    private function createTestApplication(): CandidateApplication
    {
        $branch = Branch::first();
        /** @var JobOpeningService $openingService */
        $openingService = $this->app->make(JobOpeningService::class);
        /** @var CandidateRegistrationService $candidateService */
        $candidateService = $this->app->make(CandidateRegistrationService::class);

        $opening = $openingService->createOpening([
            'title'     => 'Phase 3 Opening ' . uniqid(),
            'branch_id' => $branch->id,
            'headcount' => 2,
            'opened_at' => date('Y-m-d'),
            'status'    => 'open',
        ], $this->admin);
        $this->cleanupOpeningIds[] = $opening->id;

        $app = $candidateService->registerCandidate([
            'full_name'      => 'Phase 3 Candidate ' . uniqid(),
            'email'          => 'p3_' . uniqid() . '@example.com',
            'phone'          => '08123456789',
            'job_opening_id' => $opening->id,
            'source'         => 'LinkedIn',
            'consent'        => '1',
        ], 'hr', $this->admin);
        $this->cleanupCandidateIds[] = $app->candidate_id;

        return $app;
    }

    public function test_services_are_bound_to_implementations(): void
    {
        $assessmentService = $this->app->make(RecruitmentAssessmentService::class);
        $this->assertInstanceOf(RecruitmentAssessmentImplement::class, $assessmentService);

        $interviewService = $this->app->make(RecruitmentInterviewService::class);
        $this->assertInstanceOf(RecruitmentInterviewImplement::class, $interviewService);
    }

    public function test_psychotest_upload_pdf_success_and_secure_download(): void
    {
        /** @var RecruitmentAssessmentService $service */
        $service = $this->app->make(RecruitmentAssessmentService::class);
        $application = $this->createTestApplication();

        // 1. Upload valid PDF assessment report
        $fakePdf = UploadedFile::fake()->create('psychotest_report.pdf', 500, 'application/pdf');

        $assessment = $service->uploadAssessment(
            $application,
            [
                'conclusion'  => 'recommended',
                'assessed_at' => date('Y-m-d'),
                'notes'       => 'Strong cognitive ability and high emotional stability.',
            ],
            $fakePdf,
            $this->admin
        );

        $this->assertInstanceOf(ApplicationAssessment::class, $assessment);
        $this->assertSame('recommended', $assessment->conclusion);
        $this->assertSame('Recommended', $assessment->conclusion_label);
        $this->assertSame('success', $assessment->conclusion_badge_class);
        Storage::disk('local')->assertExists($assessment->file_path);

        // 2. Download via route authorized as Admin -> 200 OK
        $response = $this->actingAs($this->admin)->get(route('recruitment.assessments.download', [
            'candidate'   => $application->candidate_id,
            'application' => $application->id,
            'assessment'  => $assessment->id,
        ]));
        $response->assertOk();

        // 3. Unauthorized non-HR user downloading -> 403 Forbidden
        [$staffUser] = $this->createTestUserWithEmployee('Teacher Staff', roleId: 0); // No recruitment role
        $unauthResponse = $this->actingAs($staffUser)->get(route('recruitment.assessments.download', [
            'candidate'   => $application->candidate_id,
            'application' => $application->id,
            'assessment'  => $assessment->id,
        ]));
        $unauthResponse->assertForbidden();

        // 4. Non-PDF file upload rejected with validation error
        $fakeTxt = UploadedFile::fake()->create('psychotest.txt', 100, 'text/plain');
        $this->expectException(ValidationException::class);
        $service->uploadAssessment(
            $application,
            ['conclusion' => 'recommended'],
            $fakeTxt,
            $this->admin
        );
    }

    public function test_candidate_show_page_renders_psychotest_and_interview_panels(): void
    {
        $application = $this->createTestApplication();
        [$interviewerUser, $interviewerEmp] = $this->createTestUserWithEmployee('Panel Interviewer');

        $response = $this->actingAs($this->admin)->get(route('recruitment.candidates.show', $application->candidate_id));
        $response->assertOk();
        $response->assertSee('Psychotest Assessments');
        $response->assertSee('Interview Rounds');
    }

    public function test_scheduling_interview_requires_employee_with_system_login_account(): void
    {
        /** @var RecruitmentInterviewService $service */
        $service = $this->app->make(RecruitmentInterviewService::class);
        $application = $this->createTestApplication();

        // 1. Employee WITHOUT user account
        $branch = Branch::first();
        $personal = Personal::create([
            'first_name'  => 'Unlinked',
            'last_name'   => 'Employee',
            'fullname'    => 'Unlinked Employee',
            'email'       => 'unlinked_' . uniqid() . '@example.com',
            'religion_id' => 1,
        ]);
        $empWithoutUser = Employee::create([
            'user_id'       => null,
            'personal_id'   => $personal->id,
            'employment_id' => Employment::create(['branch_id' => $branch->id, 'status' => true])->id,
            'is_active'     => true,
        ]);

        $failedValidation = false;
        try {
            $service->scheduleRound(
                $application,
                [
                    'round_type'   => 'user',
                    'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
                ],
                [$empWithoutUser->id],
                $this->admin
            );
        } catch (ValidationException $e) {
            $failedValidation = true;
            $this->assertStringContainsString('does not have an active system login account', $e->getMessage());
        }
        $this->assertTrue($failedValidation, 'Scheduling must fail when selected interviewer has no system login account.');

        // 2. Employee WITH user account -> succeeds!
        [$interviewerUser, $interviewerEmp] = $this->createTestUserWithEmployee('Valid Interviewer');

        $round = $service->scheduleRound(
            $application,
            [
                'round_type'       => 'user',
                'scheduled_at'     => now()->addDays(2)->format('Y-m-d H:i:s'),
                'location_or_link' => 'https://meet.google.com/abc-defg-hij',
                'notes'            => 'Focus on curriculum experience.',
            ],
            [$interviewerEmp->id],
            $this->admin
        );

        $this->assertInstanceOf(InterviewRound::class, $round);
        $this->assertSame(1, $round->round_no);
        $this->assertSame('scheduled', $round->status);
        $this->assertTrue($round->interviewers->contains('id', $interviewerEmp->id));

        // Candidate application stage advanced to interview
        $application->refresh();
        $this->assertSame('interview', $application->stage);
        $this->assertSame('scheduled', $application->stage_substatus);

        // User is recognized as assigned interviewer
        $this->assertTrue($interviewerUser->isAssignedInterviewer());
    }

    public function test_interviewer_isolation_interviewer_a_cannot_access_interviewer_b_round(): void
    {
        /** @var RecruitmentInterviewService $service */
        $service = $this->app->make(RecruitmentInterviewService::class);
        $application = $this->createTestApplication();

        [$userA, $empA] = $this->createTestUserWithEmployee('Interviewer Alpha', roleId: 0);
        [$userB, $empB] = $this->createTestUserWithEmployee('Interviewer Beta', roleId: 0);

        // Schedule round with Interviewer A only
        $roundA = $service->scheduleRound(
            $application,
            [
                'round_type'   => 'hr',
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ],
            [$empA->id],
            $this->admin
        );

        // Interviewer A accesses own round -> 200 OK
        $responseA = $this->actingAs($userA)->get(route('recruitment.my-interviews.show', $roundA->id));
        $responseA->assertOk();
        $responseA->assertSee('Interviewer Alpha');

        // Interviewer B attempts to access Interviewer A's round -> 403 Forbidden!
        $responseB = $this->actingAs($userB)->get(route('recruitment.my-interviews.show', $roundA->id));
        $responseB->assertForbidden();
    }

    public function test_blind_review_interviewer_cannot_see_peer_evaluations_before_submitting(): void
    {
        /** @var RecruitmentInterviewService $service */
        $service = $this->app->make(RecruitmentInterviewService::class);
        $application = $this->createTestApplication();

        [$userA, $empA] = $this->createTestUserWithEmployee('Panelist One', roleId: 0);
        [$userB, $empB] = $this->createTestUserWithEmployee('Panelist Two', roleId: 0);

        // Schedule panel round with BOTH Panelist One and Panelist Two
        $round = $service->scheduleRound(
            $application,
            [
                'round_type'   => 'panel',
                'scheduled_at' => now()->addHours(3)->format('Y-m-d H:i:s'),
            ],
            [$empA->id, $empB->id],
            $this->admin
        );

        $competencies = EvaluationCompetency::active()->get();
        $this->assertGreaterThanOrEqual(1, $competencies->count());

        $scores = [];
        foreach ($competencies as $c) {
            $scores[$c->id] = 4;
        }

        // Panelist Two submits evaluation first
        $service->submitEvaluation(
            $round,
            $empB,
            [
                'overall_recommendation' => 'strongly_recommend',
                'strengths'              => 'Superb pedagogical knowledge.',
                'concerns'               => 'None noted.',
                'scores'                 => $scores,
            ]
        );

        // Panelist One has NOT submitted yet.
        // Blind review rule: Panelist One MUST NOT see peer evaluations!
        $summaryBeforeSubmit = $service->getEvaluationSummaryForInterviewer($round, $empA);
        $this->assertNull($summaryBeforeSubmit, 'Interviewer must not see peer evaluations before submitting their own.');

        // Panelist One views page in browser -> peer feedback must not be present
        $viewResponse = $this->actingAs($userA)->get(route('recruitment.my-interviews.show', $round->id));
        $viewResponse->assertOk();
        $viewResponse->assertDontSee('Superb pedagogical knowledge.');

        // Now Panelist One submits their evaluation
        $service->submitEvaluation(
            $round,
            $empA,
            [
                'overall_recommendation' => 'recommend',
                'strengths'              => 'Solid classroom management.',
                'concerns'               => 'Needs orientation on school ERP.',
                'scores'                 => $scores,
            ]
        );

        // After submit: Panelist One CAN see peer review summary!
        $summaryAfterSubmit = $service->getEvaluationSummaryForInterviewer($round, $empA);
        $this->assertNotNull($summaryAfterSubmit);
        $this->assertCount(1, $summaryAfterSubmit['peer_evaluations']);
        $this->assertSame('strongly_recommend', $summaryAfterSubmit['peer_evaluations']->first()->overall_recommendation);

        // Round status automatically becomes 'done' since all panel interviewers submitted!
        $round->refresh();
        $this->assertSame('done', $round->status);
    }

    public function test_offer_stage_gate_blocks_transition_if_evaluations_pending_allows_override_with_reason(): void
    {
        /** @var RecruitmentInterviewService $interviewService */
        $interviewService = $this->app->make(RecruitmentInterviewService::class);
        /** @var CandidateRegistrationService $candidateService */
        $candidateService = $this->app->make(CandidateRegistrationService::class);

        $application = $this->createTestApplication();
        [$userA, $empA] = $this->createTestUserWithEmployee('Interviewer Solo');

        // Schedule interview round
        $round = $interviewService->scheduleRound(
            $application,
            [
                'round_type'   => 'user',
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ],
            [$empA->id],
            $this->admin
        );

        // Round evaluation is currently draft (incomplete).
        // 1. Attempting to advance to 'offer' without reason MUST BE BLOCKED!
        $blocked = false;
        try {
            $candidateService->updateCandidateApplication(
                $application->candidate,
                $application,
                ['stage' => 'offer'],
                $this->admin
            );
        } catch (ValidationException $e) {
            $blocked = true;
            $this->assertStringContainsString('Advancing to Offer stage requires at least one completed interview round', $e->getMessage());
        }
        $this->assertTrue($blocked, 'Transition to offer without completed interview evaluations must be blocked.');

        // 2. Advancing to 'offer' WITH explicit HR override reason -> ALLOWED and recorded in history!
        $candidateService->updateCandidateApplication(
            $application->candidate,
            $application,
            [
                'stage'  => 'offer',
                'reason' => 'Fast-track offer requested by Principal due to competing offer.',
            ],
            $this->admin
        );

        $application->refresh();
        $this->assertSame('offer', $application->stage);

        $history = $application->stageHistories()->first();
        $this->assertSame('offer', $history->to_stage);
        $this->assertStringContainsString('Fast-track offer requested by Principal', $history->reason);
    }

    public function test_evaluation_competencies_settings_crud_and_status_toggle(): void
    {
        $compName = 'Pedagogical Agility ' . uniqid();

        // 1. Create competency via POST
        $createResponse = $this->actingAs($this->admin)->post(route('recruitment.settings.evaluation-competencies.store'), [
            'name'        => $compName,
            'description' => 'Ability to adapt teaching techniques dynamically.',
            'sort_order'  => 10,
            'is_active'   => '1',
        ]);
        $createResponse->assertRedirect(route('recruitment.settings.evaluation-competencies.index'));

        $competency = EvaluationCompetency::where('name', $compName)->first();
        $this->assertNotNull($competency);
        $this->assertTrue($competency->is_active);

        // 2. Toggle active status
        $toggleResponse = $this->actingAs($this->admin)->patch(route('recruitment.settings.evaluation-competencies.toggle', $competency->id));
        $toggleResponse->assertRedirect(route('recruitment.settings.evaluation-competencies.index'));
        $competency->refresh();
        $this->assertFalse($competency->is_active);

        // 3. Update competency
        $updateResponse = $this->actingAs($this->admin)->put(route('recruitment.settings.evaluation-competencies.update', $competency->id), [
            'name'        => $compName . ' Updated',
            'description' => 'Updated description.',
            'sort_order'  => 12,
            'is_active'   => '1',
        ]);
        $updateResponse->assertRedirect(route('recruitment.settings.evaluation-competencies.index'));
        $competency->refresh();
        $this->assertSame($compName . ' Updated', $competency->name);
        $this->assertTrue($competency->is_active);

        // 4. Delete competency
        $deleteResponse = $this->actingAs($this->admin)->delete(route('recruitment.settings.evaluation-competencies.destroy', $competency->id));
        $deleteResponse->assertRedirect(route('recruitment.settings.evaluation-competencies.index'));
        $this->assertNull(EvaluationCompetency::find($competency->id));
    }

    public function test_role3_management_user_without_monitor_cannot_access_recruitment_workspace(): void
    {
        $mgmtUser = User::create([
            'name'     => 'Principal Management User ' . uniqid(),
            'email'    => 'principal_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->cleanupUserIds[] = $mgmtUser->id;
        $mgmtUser->roles()->attach(3);

        $this->assertFalse($mgmtUser->canAccessRecruitment());
        $this->assertFalse($mgmtUser->isRecruitmentMonitor());

        $response = $this->actingAs($mgmtUser)->get(route('recruitment.candidates'));
        $response->assertStatus(403);

        $response = $this->actingAs($mgmtUser)->get(route('recruitment.job-openings.index'));
        $response->assertStatus(403);
    }
    public function test_cv_inline_view_and_absolute_path_resolution(): void
    {
        $application = $this->createTestApplication();

        // 1. Create a dummy CV on disk
        $dummyCvContent = "%PDF-1.4 test candidate cv";
        $dummyPath = 'recruitment/cv/test_cv_' . uniqid() . '.pdf';
        Storage::disk('local')->put($dummyPath, $dummyCvContent);

        $application->resume_path = $dummyPath;
        $application->save();

        // Test HR viewing CV inline
        $response = $this->actingAs($this->admin)->get(route('recruitment.candidates.resume', [
            'candidate'   => $application->candidate_id,
            'application' => $application->id,
        ]));
        $response->assertOk();
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));

        // 2. Test with simulated server absolute prefix (e.g. var/www/hub/mhis_hr/storage/app/recruitment/cv/...)
        $application->resume_path = 'var/www/hub/mhis_hr/storage/app/' . $dummyPath;
        $application->save();

        $responsePrefix = $this->actingAs($this->admin)->get(route('recruitment.candidates.resume', [
            'candidate'   => $application->candidate_id,
            'application' => $application->id,
        ]));
        $responsePrefix->assertOk();
        $this->assertStringContainsString('inline', (string) $responsePrefix->headers->get('content-disposition'));
    }

    public function test_psychotest_link_assessment_and_interviewer_visibility(): void
    {
        /** @var RecruitmentAssessmentService $assessmentService */
        $assessmentService = $this->app->make(RecruitmentAssessmentService::class);
        /** @var RecruitmentInterviewService $interviewService */
        $interviewService = $this->app->make(RecruitmentInterviewService::class);

        $application = $this->createTestApplication();

        // Set up candidate CV
        $dummyCvContent = "%PDF-1.4 test candidate cv";
        $dummyPath = 'recruitment/cv/interviewer_cv_' . uniqid() . '.pdf';
        Storage::disk('local')->put($dummyPath, $dummyCvContent);
        $application->resume_path = $dummyPath;
        $application->save();

        // 1. Create Psychotest with Google Docs link (no file upload)
        $docUrl = 'https://docs.google.com/document/d/1example_psychotest_doc/edit';
        $assessment = $assessmentService->uploadAssessment(
            $application,
            [
                'conclusion'   => 'recommended',
                'assessed_at'  => date('Y-m-d'),
                'notes'        => 'Exemplary psychological test scores. Excellent logical reasoning.',
                'document_url' => $docUrl,
            ],
            null,
            $this->admin
        );

        $this->assertInstanceOf(ApplicationAssessment::class, $assessment);
        $this->assertSame($docUrl, $assessment->document_url);
        $this->assertSame('Google Docs', $assessment->document_display_label);
        $this->assertNull($assessment->file_path);

        // 2. Schedule interview with an interviewer
        [$interviewerUser, $interviewerEmp] = $this->createTestUserWithEmployee('Assigned Interviewer', roleId: 0);
        $round = $interviewService->scheduleRound(
            $application,
            [
                'round_type'   => 'user',
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ],
            [$interviewerEmp->id],
            $this->admin
        );

        // 3. Interviewer opens evaluation page
        $evalResponse = $this->actingAs($interviewerUser)->get(route('recruitment.my-interviews.show', $round->id));
        $evalResponse->assertOk();

        // Interviewer can see CV link and Psychotest Assessment with Google Docs link
        $evalResponse->assertSee('View Candidate CV');
        $evalResponse->assertSee('Psychotest Assessment');
        $evalResponse->assertSee('Recommended');
        $evalResponse->assertSee('Exemplary psychological test scores');
        $evalResponse->assertSee('Google Docs');
        $evalResponse->assertSee($docUrl);

        // 4. Interviewer can view CV inline
        $cvResponse = $this->actingAs($interviewerUser)->get(route('recruitment.my-interviews.cv', $round->id));
        $cvResponse->assertOk();
        $this->assertStringContainsString('inline', (string) $cvResponse->headers->get('content-disposition'));

        // 5. Interviewer accesses assessment route -> redirects to external Google Docs link
        $assessResponse = $this->actingAs($interviewerUser)->get(route('recruitment.my-interviews.assessment', [
            'round'      => $round->id,
            'assessment' => $assessment->id,
        ]));
        $assessResponse->assertRedirect($docUrl);
    }
    public function test_bulk_schedule_interview_rounds_and_evaluations_for_multiple_employees(): void
    {
        /** @var RecruitmentInterviewService $interviewService */
        $interviewService = $this->app->make(RecruitmentInterviewService::class);

        $app1 = $this->createTestApplication();
        $app2 = $this->createTestApplication();

        [$interviewerUser1, $emp1] = $this->createTestUserWithEmployee('Panelist Alpha', roleId: 0);
        [$interviewerUser2, $emp2] = $this->createTestUserWithEmployee('Panelist Beta', roleId: 0);

        // Call HTTP bulk schedule endpoint
        $response = $this->actingAs($this->admin)->post(route('recruitment.interviews.bulk-schedule'), [
            'application_ids'       => [$app1->id, $app2->id],
            'round_type'            => 'panel',
            'scheduled_at'          => '2026-11-10 09:00:00',
            'slot_interval_minutes' => 30,
            'location_or_link'      => 'https://meet.google.com/test-panel',
            'notes'                 => 'Bulk scheduled panel interview',
            'interviewers'          => [$emp1->id, $emp2->id],
        ]);

        $response->assertSessionHas('success');

        // Check Application 1
        $app1->refresh();
        $this->assertSame('interview', $app1->stage);
        $this->assertSame('scheduled', $app1->stage_substatus);

        $round1 = $app1->interviewRounds()->first();
        $this->assertNotNull($round1);
        $this->assertSame('panel', $round1->round_type);
        $this->assertSame('2026-11-10 09:00:00', $round1->scheduled_at->format('Y-m-d H:i:s'));
        $this->assertCount(2, $round1->interviewers);
        $this->assertCount(2, $round1->evaluations);
        $this->assertTrue($round1->interviewers->contains('id', $emp1->id));
        $this->assertTrue($round1->interviewers->contains('id', $emp2->id));

        // Check Application 2 (Scheduled with 30 min interval)
        $app2->refresh();
        $this->assertSame('interview', $app2->stage);
        $this->assertSame('scheduled', $app2->stage_substatus);

        $round2 = $app2->interviewRounds()->first();
        $this->assertNotNull($round2);
        $this->assertSame('panel', $round2->round_type);
        $this->assertSame('2026-11-10 09:30:00', $round2->scheduled_at->format('Y-m-d H:i:s'));
        $this->assertCount(2, $round2->interviewers);
        $this->assertCount(2, $round2->evaluations);
        $this->assertTrue($round2->interviewers->contains('id', $emp1->id));
        $this->assertTrue($round2->interviewers->contains('id', $emp2->id));

        // Interviewer 1 sees both assigned rounds
        $assignedRoundsUser1 = $interviewService->getInterviewerAssignedRounds($emp1);
        $this->assertCount(2, $assignedRoundsUser1);

        // Interviewer 2 opens page and sees the candidate evaluation form
        $evalResponse = $this->actingAs($interviewerUser2)->get(route('recruitment.my-interviews.show', $round1->id));
        $evalResponse->assertOk();
    }
}
