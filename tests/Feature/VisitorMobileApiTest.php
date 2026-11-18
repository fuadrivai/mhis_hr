<?php

namespace Tests\Feature;

use App\Models\VisitorVisit;
use App\Models\User;
use App\Notifications\VisitorInvitationForGuest;
use App\Notifications\VisitorRegistrationLinkNotification;
use App\Notifications\VisitorVisitNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class VisitorMobileApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        Notification::fake();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token');
            $table->string('device')->nullable();
            $table->string('device_id')->nullable();
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active');
        });
        foreach (['organizations', 'job_levels', 'positions'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('name');
            });
        }
        Schema::create('employments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('job_level_id')->nullable();
            $table->unsignedBigInteger('job_position_id')->nullable();
        });
        Schema::create('personals', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('avatar')->nullable();
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('employment_id')->nullable();
            $table->unsignedBigInteger('personal_id')->nullable();
            $table->boolean('is_active');
        });
        foreach ([
            '2026_11_06_000001_create_visitor_categories_and_purposes_tables.php',
            '2026_11_06_000002_create_visitors_table.php',
            '2026_11_06_000003_create_visitor_visits_table.php',
            '2026_11_06_000004_create_visitor_approvals_and_logs_tables.php',
            '2026_11_13_000001_merge_visitor_notes_into_notes.php',
            '2026_11_16_000001_create_visitor_invitation_links_tables.php',
            '2026_11_17_000001_add_visitor_invitation_monitors_and_optional_fields.php',
            '2026_11_17_000002_add_invitation_link_to_visitor_visits.php',
            '2026_11_17_000003_make_visitor_registered_by_nullable.php',
            '2026_11_17_000004_add_visitor_walk_in_group_id.php',
        ] as $migration) {
            (require database_path('migrations' . DIRECTORY_SEPARATOR . $migration))->up();
        }

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Creator', 'email' => 'creator@example.test'],
            ['id' => 2, 'name' => 'Host', 'email' => 'host@example.test'],
            ['id' => 3, 'name' => 'Other', 'email' => 'other@example.test'],
        ]);
        DB::table('sessions')->insert([
            ['user_id' => 1, 'token' => 'bearer creator'],
            ['user_id' => 2, 'token' => 'bearer host'],
            ['user_id' => 3, 'token' => 'bearer other'],
        ]);
        DB::table('branches')->insert([
            ['id' => 1, 'name' => 'School', 'is_active' => true],
            ['id' => 2, 'name' => 'Other branch', 'is_active' => true],
        ]);
        DB::table('organizations')->insert(['id' => 1, 'name' => 'Operations']);
        DB::table('job_levels')->insert(['id' => 1, 'name' => 'Senior']);
        DB::table('positions')->insert(['id' => 1, 'name' => 'Director']);
        DB::table('personals')->insert([
            ['id' => 1, 'fullname' => 'Creator Person', 'avatar' => null],
            ['id' => 2, 'fullname' => 'Host Person', 'avatar' => 'avatars/host.png'],
            ['id' => 3, 'fullname' => 'Other Person', 'avatar' => null],
        ]);
        DB::table('employments')->insert([
            'id' => 1, 'branch_id' => 1, 'organization_id' => 1, 'job_level_id' => 1, 'job_position_id' => 1,
        ]);
        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => 1, 'employment_id' => 1, 'personal_id' => 1, 'is_active' => true],
            ['id' => 2, 'user_id' => 2, 'employment_id' => 1, 'personal_id' => 2, 'is_active' => true],
            ['id' => 3, 'user_id' => 3, 'employment_id' => 1, 'personal_id' => 3, 'is_active' => true],
        ]);
    }

    private function headers(string $token = 'creator'): array
    {
        return ['Authorization' => 'bearer ' . $token, 'Accept' => 'application/json'];
    }

    private function invitation(array $overrides = []): array
    {
        $date = now()->addDay();

        return array_merge([
            'full_name' => 'Visitor One',
            'phone' => '08123456789',
            'visitor_category_id' => 1,
            'visitor_purpose_id' => 1,
            'branch_id' => 1,
            'host_employee_id' => 2,
            'visit_date' => $date->format('Y-m-d'),
            'expected_arrival_time' => '09:00',
            'expected_departure_time' => '10:00',
        ], $overrides);
    }

    public function test_create_invitation_for_another_host_and_view_details_as_creator(): void
    {
        $response = $this->postJson('/api/visitor/invitations', $this->invitation(['notes' => 'Discuss contract']), $this->headers())
            ->assertCreated()->assertJsonPath('data.host_employee_id', 2)
            ->assertJsonStructure(['data' => ['id', 'visit_code', 'status']]);
        $id = $response->json('data.id');
        $visitDate = \Carbon\Carbon::parse($response->json('data.visit_date'));

        $this->assertDatabaseHas('visitor_visits', ['id' => $id, 'registered_by' => 1, 'host_employee_id' => 2]);
        $this->getJson('/api/visitor/invitations/' . $id, $this->headers())
            ->assertOk()->assertJsonPath('data.visitor.full_name', 'Visitor One')
            ->assertJsonPath('data.visit_date', $visitDate->toDateString())
            ->assertJsonPath('data.expected_arrival_at', $visitDate->copy()->setTime(9, 0)->toIso8601String())
            ->assertJsonPath('data.expected_departure_at', $visitDate->copy()->setTime(10, 0)->toIso8601String())
            ->assertJsonPath('data.notes', 'Discuss contract')
            ->assertJsonMissingPath('data.visitor_notes')
            ->assertJsonPath('data.host.name', 'Host')->assertJsonPath('data.can_cancel', true)
            ->assertJsonMissingPath('data.qr_token_hash')->assertJsonMissingPath('data.metadata');
        $this->getJson('/api/visitor/invitations/' . $id, $this->headers('host'))
            ->assertOk()->assertJsonPath('data.can_cancel', false);
        $this->getJson('/api/visitor/invitations/' . $id, $this->headers('other'))->assertNotFound();
        $this->postJson('/api/visitor/invitations/' . $id . '/cancel', ['reason' => 'Changed'], $this->headers('host'))
            ->assertNotFound();
        $this->postJson('/api/visitor/invitations/' . $id . '/cancel', ['reason' => 'Changed'], $this->headers('other'))
            ->assertNotFound();
    }

    public function test_invitation_can_be_monitored_by_selected_employees_without_optional_details(): void
    {
        Storage::fake('local');
        DB::table('employments')->insert([
            'id' => 2,
            'branch_id' => 2,
            'organization_id' => 1,
            'job_level_id' => 1,
            'job_position_id' => 1,
        ]);
        DB::table('employees')->where('id', 3)->update(['employment_id' => 2]);
        $visitDate = now()->addDay()->toDateString();
        $response = $this->post('/api/visitor/invitations', [
            'full_name' => 'Monitored Visitor',
            'phone' => '08123456789',
            'branch_id' => 1,
            'host_employee_id' => 2,
            'visit_date' => $visitDate,
            'expected_arrival_time' => '09:00',
            'monitor_employee_ids' => [1, 3],
            'attachment' => UploadedFile::fake()->create('visitor-document.pdf', 25, 'application/pdf'),
        ], $this->headers())->assertCreated();

        $visitId = $response->json('data.id');
        $this->assertDatabaseHas('visitor_visits', [
            'id' => $visitId,
            'visitor_category_id' => null,
            'visitor_purpose_id' => null,
            'expected_departure_at' => null,
        ]);
        $this->assertDatabaseHas('visitors', [
            'full_name' => 'Monitored Visitor',
        ]);
        $visitor = DB::table('visitors')->where('full_name', 'Monitored Visitor')->first();
        $this->assertStringEndsWith('.pdf', $visitor->photo_path);
        $this->assertDatabaseHas('visitor_visit_monitors', [
            'visitor_visit_id' => $visitId,
            'employee_id' => 1,
        ]);
        $this->assertDatabaseHas('visitor_visit_monitors', [
            'visitor_visit_id' => $visitId,
            'employee_id' => 3,
        ]);

        $this->getJson('/api/visitor/monitoring', $this->headers('creator'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $visitId)
            ->assertJsonPath('data.0.visitor.full_name', 'Monitored Visitor');
        $this->getJson('/api/visitor/monitoring', $this->headers('other'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $visitId);
        $this->getJson('/api/visitor/invitations/' . $visitId, $this->headers('other'))
            ->assertOk()
            ->assertJsonPath('data.category', null)
            ->assertJsonPath('data.purpose', null)
            ->assertJsonPath('data.expected_departure_at', null);
    }

    public function test_monitor_defaults_follow_host_selection_and_are_optional(): void
    {
        $visit = $this->invitation(['host_employee_id' => 2]);
        $defaultMonitorVisitId = $this->postJson('/api/visitor/invitations', $visit, $this->headers())
            ->assertCreated()->json('data.id');
        $this->assertDatabaseHas('visitor_visit_monitors', [
            'visitor_visit_id' => $defaultMonitorVisitId,
            'employee_id' => 1,
        ]);

        $visit = $this->invitation([
            'full_name' => 'No Monitor Visitor',
            'host_employee_id' => 2,
            'monitor_employee_ids' => [],
        ]);
        $noMonitorVisitId = $this->postJson('/api/visitor/invitations', $visit, $this->headers())
            ->assertCreated()->json('data.id');
        $this->assertDatabaseMissing('visitor_visit_monitors', [
            'visitor_visit_id' => $noMonitorVisitId,
        ]);

        $visit = $this->invitation([
            'host_employee_id' => 1,
            'monitor_employee_ids' => [1],
        ]);
        $this->postJson('/api/visitor/invitations', $visit, $this->headers())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('monitor_employee_ids');
    }

    public function test_invitation_request_rejects_departure_date_without_time(): void
    {
        $visit = $this->invitation([
            'expected_departure_date' => now()->addDay()->toDateString(),
            'expected_departure_time' => null,
        ]);
        $this->postJson('/api/visitor/invitations', $visit, $this->headers())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('expected_departure_time');
    }

    public function test_guest_registration_links_support_manual_sharing_and_email_delivery(): void
    {
        $visitDate = now()->addDays(2);
        $response = $this->actingAs(\App\Models\User::findOrFail(1))
            ->post(route('visitor.invitation-links.store'), [
                'name' => 'Interview Day',
                'host_employee_id' => 2,
                'branch_id' => 1,
                'visitor_category_id' => 1,
                'visitor_purpose_id' => 1,
                'visit_date' => $visitDate->toDateString(),
                'expected_arrival_time' => '09:00',
                'expected_departure_time' => '09:30',
                'notes' => 'Please report to reception.',
                'share_link_count' => 1,
                'recipient_emails_input' => "candidate@example.test\n",
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('visitor_invitation_batches', ['name' => 'Interview Day', 'created_by' => 1]);
        $this->assertDatabaseCount('visitor_invitation_links', 2);
        $batchId = DB::table('visitor_invitation_batches')->where('name', 'Interview Day')->value('id');
        Notification::assertSentOnDemand(VisitorRegistrationLinkNotification::class);
        $links = DB::table('visitor_invitation_links')->orderBy('id')->get()->map(fn ($link) => [
            'url' => route('visitor.public-registration.show', [
                'token' => \Illuminate\Support\Facades\Crypt::decryptString($link->token_encrypted),
            ]),
            'recipient_email' => $link->recipient_email,
        ])->all();
        $this->assertCount(2, $links);
        $this->assertSame('candidate@example.test', $links[0]['recipient_email']);
        $firstStoredLink = DB::table('visitor_invitation_links')->orderBy('id')->first();
        $token = basename(parse_url($links[0]['url'], PHP_URL_PATH));
        $this->assertSame(hash('sha256', $token), $firstStoredLink->token_hash);
        $this->assertNotSame($token, $firstStoredLink->token_encrypted);
        $this->actingAs(\App\Models\User::findOrFail(3))
            ->get(route('visitor.invitation-links.show', $batchId))
            ->assertNotFound();
        $this->get(route('visitor.public-registration.show', ['token' => $token]))
            ->assertOk()
            ->assertSee('Complete Visitor Registration')
            ->assertSee('images/logo mh menyamping putih-01-01.png')
            ->assertSee('name="full_name"', false)
            ->assertSee('I am bringing friends or colleagues')
            ->assertSee('Confirm and submit')
            ->assertDontSee('Please report to reception.');

        $registrationUrl = route('visitor.public-registration.submit', ['token' => $token]);
        $this->from(route('visitor.public-registration.show', ['token' => $token]))
            ->post($registrationUrl, [
                'full_name' => 'Interview Candidate',
                'phone' => '08123456789',
                'email' => 'candidate@example.test',
                'company' => 'Example Company',
                'bring_group' => '1',
                'group_members' => [],
            ])
            ->assertRedirect(route('visitor.public-registration.show', ['token' => $token]))
            ->assertSessionHasErrors('group_members');

        $registrationResponse = $this->post($registrationUrl, [
            'full_name' => 'Interview Candidate',
            'phone' => '08123456789',
            'email' => 'candidate@example.test',
            'company' => 'Example Company',
            'bring_group' => '1',
            'group_members' => [
                ['full_name' => 'Candidate Colleague One', 'phone' => '08111111111'],
                ['full_name' => 'Candidate Colleague Two', 'email' => 'colleague@example.test'],
            ],
        ]);
        $registrationResponse->assertOk()
            ->assertSee('Registration complete')
            ->assertSee('images/logo mh menyamping putih-01-01.png');
        $linkId = DB::table('visitor_invitation_links')->where('token_hash', hash('sha256', $token))->value('id');
        $primaryVisitId = DB::table('visitor_invitation_links')->where('id', $linkId)->value('visitor_visit_id');
        $primaryVisitCode = DB::table('visitor_visits')->where('id', $primaryVisitId)->value('visit_code');
        $registrationResponse->assertSee($primaryVisitCode);
        foreach (DB::table('visitor_visits')->where('visitor_invitation_link_id', $linkId)->where('id', '<>', $primaryVisitId)->get() as $groupVisit) {
            $registrationResponse->assertDontSee($groupVisit->visit_code);
        }
        $this->get(route('visitor.public-registration.show', ['token' => $token]))
            ->assertStatus(410)
            ->assertSee('This link is no longer available')
            ->assertSee('images/logo mh menyamping putih-01-01.png');

        $this->assertNotNull(DB::table('visitor_invitation_links')->where('id', $linkId)->value('submitted_at'));
        $this->assertDatabaseHas('visitor_visits', [
            'registered_by' => 1,
            'host_employee_id' => 2,
            'registration_method' => 'shared_invitation',
        ]);
        $submittedLink = \App\Models\VisitorInvitationLink::with('visit.visitor')->findOrFail($linkId);
        $this->assertSame('Interview Candidate', $submittedLink->visit->visitor->full_name);
        $this->assertSame('08123456789', $submittedLink->visit->visitor->phone);
        $this->assertSame('candidate@example.test', $submittedLink->visit->visitor->email);
        $this->assertSame('Example Company', $submittedLink->visit->visitor->company);
        $this->assertCount(3, $submittedLink->visits);
        $this->assertSame(
            3,
            DB::table('visitor_visits')->where('visitor_invitation_link_id', $linkId)->count()
        );
        $this->assertDatabaseCount('visitor_visits', 3);
        $this->assertDatabaseHas('visitors', [
            'full_name' => 'Candidate Colleague One',
            'phone' => '08111111111',
        ]);
        $this->assertDatabaseHas('visitors', [
            'full_name' => 'Candidate Colleague Two',
            'email' => 'colleague@example.test',
        ]);
        $this->getJson('/api/visitor/invitation-links/' . $batchId, $this->headers())
            ->assertOk()
            ->assertJsonFragment(['full_name' => 'Interview Candidate'])
            ->assertJsonFragment(['full_name' => 'Candidate Colleague One'])
            ->assertJsonFragment(['full_name' => 'Candidate Colleague Two']);
        $this->post(route('visitor.public-registration.submit', ['token' => $token]), [
            'full_name' => 'Second Submission',
            'phone' => '08999999999',
        ])->assertStatus(410)->assertSee('This link is no longer available');

        $manualToken = basename(parse_url($links[1]['url'], PHP_URL_PATH));
        $manualLinkId = DB::table('visitor_invitation_links')->where('token_hash', hash('sha256', $manualToken))->value('id');
        $this->actingAs(\App\Models\User::findOrFail(1))
            ->post(route('visitor.invitation-links.recipient-email', $manualLinkId), [
                'recipient_email' => 'manual-candidate@example.test',
            ])
            ->assertRedirect();
        $this->assertDatabaseHas('visitor_invitation_links', [
            'id' => $manualLinkId,
            'recipient_email' => 'manual-candidate@example.test',
        ]);
        $this->actingAs(\App\Models\User::findOrFail(3))
            ->post(route('visitor.invitation-links.recipient-email', $manualLinkId), [
                'recipient_email' => 'unauthorized@example.test',
            ])
            ->assertNotFound();
        $this->actingAs(\App\Models\User::findOrFail(1))
            ->post(route('visitor.invitation-links.revoke', $manualLinkId))
            ->assertRedirect();
        $this->get(route('visitor.public-registration.show', ['token' => $manualToken]))
            ->assertStatus(410)
            ->assertSee('This link is no longer available');
    }

    public function test_mobile_api_creates_guest_registration_links_and_returns_share_urls(): void
    {
        $visitDate = now()->addDays(3);

        $response = $this->postJson('/api/visitor/invitation-links', [
            'name' => 'Mobile Interview Day',
            'host_employee_id' => 2,
            'branch_id' => 1,
            'visitor_category_id' => 1,
            'visitor_purpose_id' => 1,
            'visit_date' => $visitDate->toDateString(),
            'expected_arrival_time' => '09:00',
            'expected_departure_time' => '10:00',
            'share_link_count' => 1,
            'recipient_emails' => ['candidate@example.test'],
        ], $this->headers())
            ->assertCreated()
            ->assertJsonPath('message', 'Guest registration links created.')
            ->assertJsonPath('data.name', 'Mobile Interview Day')
            ->assertJsonPath('data.links.0.recipient_email', 'candidate@example.test')
            ->assertJsonPath('data.links.0.delivery_status', 'sent')
            ->assertJsonPath('data.links.1.recipient_email', null)
            ->assertJsonPath('data.links.1.delivery_status', 'manual')
            ->assertJsonPath('email_failures', [])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'visit_date',
                    'expires_at',
                    'links' => [
                        ['id', 'recipient_email', 'delivery_status', 'url'],
                    ],
                ],
            ]);

        $batchId = $response->json('data.id');
        $this->getJson('/api/visitor/invitation-links', $this->headers())
            ->assertOk()
            ->assertJsonPath('data.0.id', $batchId)
            ->assertJsonPath('data.0.name', 'Mobile Interview Day')
            ->assertJsonPath('data.0.links_count', 2)
            ->assertJsonPath('data.0.submitted_links_count', 0);
        $this->getJson('/api/visitor/invitation-links/' . $batchId, $this->headers())
            ->assertOk()
            ->assertJsonPath('data.id', $batchId)
            ->assertJsonPath('data.links.0.status', 'waiting_for_guest')
            ->assertJsonPath('data.links.0.recipient_email', 'candidate@example.test')
            ->assertJsonPath('data.links.1.status', 'waiting_for_guest')
            ->assertJsonPath('data.links.1.recipient_email', null)
            ->assertJsonPath('data.links.1.visitor', null)
            ->assertJsonStructure([
                'data' => [
                    'branch' => ['id', 'name'],
                    'host' => ['employee_id', 'name'],
                    'links' => [
                        ['id', 'recipient_email', 'status', 'submitted_at', 'url', 'visit_code', 'visitor'],
                    ],
                ],
            ]);
        $this->getJson('/api/visitor/invitation-links/' . $batchId, $this->headers('other'))
            ->assertNotFound();

        $this->assertDatabaseCount('visitor_invitation_batches', 1);
        $this->assertDatabaseCount('visitor_invitation_links', 2);
        Notification::assertSentOnDemand(VisitorRegistrationLinkNotification::class);
    }

    public function test_guest_registration_email_uses_the_visitor_invitation_template(): void
    {
        $batch = \App\Models\VisitorInvitationBatch::create([
            'created_by' => 1,
            'name' => 'Open House',
            'branch_id' => 1,
            'host_employee_id' => 2,
            'visitor_category_id' => 1,
            'visitor_purpose_id' => 1,
            'visit_date' => now()->addDay()->toDateString(),
            'expected_arrival_at' => now()->addDay()->setTime(9, 0),
            'expected_departure_at' => now()->addDay()->setTime(10, 0),
            'expires_at' => now()->addDay()->endOfDay(),
        ]);
        $notification = new VisitorRegistrationLinkNotification(
            $batch->load(['branch', 'host.user', 'purpose']),
            'https://example.test/visitor/register/sample-token'
        );
        $mail = $notification->toMail(new \stdClass());

        $this->assertSame('email-template.visitor-notification', $mail->view['html']);
        $html = $this->renderVisitorMail($mail);
        $this->assertStringContainsString('Complete Your Visitor Registration', $html);
        $this->assertStringContainsString('Open House', $html);
        $this->assertStringContainsString('Complete Visitor Information', $html);
        $this->assertStringContainsString('https://example.test/visitor/register/sample-token', $html);
        $this->assertStringContainsString('This link can only be used once', $html);
    }

    public function test_legacy_visitor_notes_are_merged_into_notes_before_column_removal(): void
    {
        $id = $this->postJson('/api/visitor/invitations', $this->invitation(['notes' => 'Visit purpose']), $this->headers())
            ->assertCreated()->json('data.id');

        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->text('visitor_notes')->nullable();
        });
        DB::table('visitor_visits')->where('id', $id)->update(['visitor_notes' => 'Reception detail']);

        (require database_path('migrations' . DIRECTORY_SEPARATOR . '2026_11_13_000001_merge_visitor_notes_into_notes.php'))->up();

        $this->assertSame("Visit purpose\nReception detail", DB::table('visitor_visits')->where('id', $id)->value('notes'));
        $this->assertFalse(Schema::hasColumn('visitor_visits', 'visitor_notes'));
    }

    public function test_categories_requiring_information_accept_only_notes(): void
    {
        DB::table('visitor_categories')->where('id', 1)->update(['requires_additional_info' => true]);

        $this->postJson('/api/visitor/invitations', $this->invitation([
            'visitor_notes' => 'Legacy field',
        ]), $this->headers())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['notes']);

        $this->postJson('/api/visitor/invitations', $this->invitation([
            'notes' => 'Please prepare a visitor badge.',
        ]), $this->headers())
            ->assertCreated();
    }

    public function test_mobile_invitation_supports_departure_on_the_following_date(): void
    {
        $visitDate = now()->addDay();
        $departureDate = $visitDate->copy()->addDay();
        $id = $this->postJson('/api/visitor/invitations', [
            'full_name' => 'Overnight Vendor',
            'phone' => '08123456789',
            'visitor_category_id' => 1,
            'visitor_purpose_id' => 1,
            'branch_id' => 1,
            'host_employee_id' => 2,
            'visit_date' => $visitDate->format('Y-m-d'),
            'expected_arrival_time' => '21:00',
            'expected_departure_date' => $departureDate->format('Y-m-d'),
            'expected_departure_time' => '03:00',
        ], $this->headers())->assertCreated()->json('data.id');

        $this->getJson('/api/visitor/invitations/' . $id, $this->headers())
            ->assertOk()
            ->assertJsonPath('data.visit_date', $visitDate->toDateString())
            ->assertJsonPath('data.expected_arrival_at', $visitDate->copy()->setTime(21, 0)->toIso8601String())
            ->assertJsonPath('data.expected_departure_at', $departureDate->copy()->setTime(3, 0)->toIso8601String());

        $this->postJson('/api/visitor/invitations', [
            'full_name' => 'Invalid Overnight Vendor',
            'phone' => '08987654321',
            'visitor_category_id' => 1,
            'visitor_purpose_id' => 1,
            'branch_id' => 1,
            'host_employee_id' => 2,
            'visit_date' => $visitDate->format('Y-m-d'),
            'expected_arrival_time' => '21:00',
            'expected_departure_date' => $visitDate->copy()->subDay()->format('Y-m-d'),
            'expected_departure_time' => '03:00',
        ], $this->headers())->assertUnprocessable();
    }

    public function test_list_filters_search_pagination_and_separate_host_and_creator_scopes(): void
    {
        $first = $this->postJson('/api/visitor/invitations', $this->invitation(), $this->headers())->assertCreated()->json('data.id');
        $other = $this->postJson('/api/visitor/invitations', $this->invitation(['full_name' => 'Other Guest', 'phone' => '0811111111']), $this->headers('other'))
            ->assertCreated()->json('data.id');
        $this->assertNotSame($first, $other);
        $this->getJson('/api/visitor/invitations', $this->headers())->assertOk()->assertJsonPath('total', 0);
        $this->getJson('/api/visitor/invitations', $this->headers('host'))
            ->assertOk()->assertJsonPath('total', 2)->assertJsonPath('data.0.can_cancel', false);
        $this->getJson('/api/visitor/monitoring', $this->headers())
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $first)
            ->assertJsonPath('data.0.can_cancel', true);
        $this->getJson('/api/visitor/monitoring', $this->headers('other'))
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $other);
        $this->getJson('/api/visitor/monitoring', $this->headers('host'))->assertOk()->assertJsonPath('total', 0);

        foreach (['/api/visitor/invitations' => 'host', '/api/visitor/monitoring' => 'creator'] as $path => $user) {
            $headers = $this->headers($user);
            $this->getJson($path . '?search=Visitor&view=upcoming&per_page=1', $headers)
                ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $first);
            $this->getJson($path . '?view=today', $headers)->assertOk()->assertJsonPath('total', 0);
            $this->getJson($path . '?status=expected&from=' . now()->addDay()->toDateString() . '&search=Visitor', $headers)
                ->assertOk()->assertJsonPath('total', 1);
            $this->getJson($path . '?to=' . now()->addDays(2)->toDateString() . '&search=Visitor', $headers)
                ->assertOk()->assertJsonPath('total', 1);
            $this->getJson($path . '?status=invalid', $headers)->assertUnprocessable();
            $this->getJson($path . '?per_page=101', $headers)->assertUnprocessable();
        }
        $this->getJson('/api/visitor/invitations?employee_id=2&user[id]=2', $this->headers())
            ->assertOk()->assertJsonPath('total', 0);
        $this->getJson('/api/visitor/monitoring?employee_id=3&user[id]=3', $this->headers())
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $first);
    }

    public function test_host_and_monitoring_lists_support_name_email_phone_and_visit_date_filters(): void
    {
        $firstDate = now()->addDays(2);
        $secondDate = now()->addDays(3);
        $firstId = $this->postJson('/api/visitor/invitations', $this->invitation([
            'full_name' => 'Unique Visitor',
            'email' => 'unique@example.test',
            'phone' => '0891111111',
            'visit_date' => $firstDate->format('Y-m-d'),
            'expected_arrival_date' => $firstDate->format('d F Y'),
            'expected_departure_date' => $firstDate->format('d F Y'),
        ]), $this->headers())->assertCreated()->json('data.id');
        $secondId = $this->postJson('/api/visitor/invitations', $this->invitation([
            'full_name' => 'Another Visitor',
            'email' => 'another@example.test',
            'phone' => '0892222222',
            'visit_date' => $secondDate->format('Y-m-d'),
            'expected_arrival_date' => $secondDate->format('d F Y'),
            'expected_departure_date' => $secondDate->format('d F Y'),
        ]), $this->headers())->assertCreated()->json('data.id');

        foreach ([
            '/api/visitor/invitations' => 'host',
            '/api/visitor/monitoring' => 'creator',
        ] as $path => $user) {
            $headers = $this->headers($user);
            foreach ([
                'query=Unique%20Visitor' => $firstId,
                'query=unique%40example.test' => $firstId,
                'query=891111111' => $firstId,
                'visit_date=' . $firstDate->format('Y-m-d') => $firstId,
                'query=Another&visit_date=' . $secondDate->format('Y-m-d') => $secondId,
            ] as $filters => $expectedId) {
                $this->getJson($path . '?' . $filters, $headers)
                    ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $expectedId);
            }
            $this->getJson($path . '?visit_date=invalid', $headers)->assertUnprocessable();
        }
    }

    public function test_departure_after_filter_excludes_finished_visits_and_sorts_by_arrival(): void
    {
        $visitDate = now()->addDay();
        $visits = [
            ['name' => 'Arrives First', 'arrival' => '10:00', 'departure' => '11:00'],
            ['name' => 'Arrives Second', 'arrival' => '11:00', 'departure' => '12:00'],
            ['name' => 'Already Departed', 'arrival' => '08:00', 'departure' => '09:00'],
        ];

        foreach ($visits as $index => $visit) {
            $this->postJson('/api/visitor/invitations', $this->invitation([
                'full_name' => $visit['name'],
                'phone' => '08' . str_pad((string) ($index + 1), 9, '0', STR_PAD_LEFT),
                'visit_date' => $visitDate->format('Y-m-d'),
                'expected_arrival_date' => $visitDate->format('d F Y'),
                'expected_arrival_time' => $visit['arrival'],
                'expected_departure_date' => $visitDate->format('d F Y'),
                'expected_departure_time' => $visit['departure'],
            ]), $this->headers())->assertCreated();
        }

        $departureAfter = $visitDate->copy()->setTime(9, 0)->toIso8601String();
        foreach ([
            '/api/visitor/invitations' => 'host',
            '/api/visitor/monitoring' => 'creator',
        ] as $path => $user) {
            $url = $path . '?visit_date=' . $visitDate->format('Y-m-d')
                . '&departure_after=' . rawurlencode($departureAfter);
            $response = $this->getJson($url, $this->headers($user))
                ->assertOk()->assertJsonPath('total', 2);

            $this->assertSame(
                ['Arrives First', 'Arrives Second'],
                array_column(array_column($response->json('data'), 'visitor'), 'full_name')
            );
        }

        $this->getJson('/api/visitor/invitations?departure_after=not-a-date', $this->headers('host'))
            ->assertUnprocessable();
    }

    public function test_creator_can_cancel_only_allowed_status_and_history_includes_it(): void
    {
        $id = $this->postJson('/api/visitor/invitations', $this->invitation(), $this->headers())->assertCreated()->json('data.id');
        $this->postJson('/api/visitor/invitations/' . $id . '/cancel', [], $this->headers())->assertUnprocessable();
        $this->postJson('/api/visitor/invitations/' . $id . '/cancel', ['reason' => 'Rescheduled'], $this->headers())
            ->assertOk()->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.cancellation_reason', 'Rescheduled')->assertJsonPath('data.can_cancel', false);
        $this->postJson('/api/visitor/invitations/' . $id . '/cancel', ['reason' => 'Again'], $this->headers())
            ->assertUnprocessable();
        $this->getJson('/api/visitor/monitoring?view=history', $this->headers())
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $id);
        $this->getJson('/api/visitor/invitations?view=history', $this->headers('host'))
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $id);
    }

    public function test_form_options_and_hosts_are_scoped_and_require_authentication(): void
    {
        $this->getJson('/api/visitor/form-options', $this->headers())->assertOk()
            ->assertJsonPath('data.default_branch_id', 1)
            ->assertJsonPath('data.default_host_employee_id', 1)
            ->assertJsonCount(1, 'data.branches')->assertJsonFragment(['name' => 'Parent / Guardian']);
        $this->getJson('/api/visitor/hosts?search=Host', $this->headers())
            ->assertOk()->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', 2)
            ->assertJsonPath('data.0.name', 'Host Person')
            ->assertJsonPath('data.0.branch', 'School')
            ->assertJsonPath('data.0.level', 'Senior')
            ->assertJsonPath('data.0.position', 'Director')
            ->assertJsonPath('data.0.organization', 'Operations')
            ->assertJsonPath('data.0.avatar', asset('storage/avatars/host.png'));
        $this->getJson('/api/visitor/hosts?search=Host%20Person', $this->headers())
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', 2);
        $this->getJson('/api/visitor/invitations')->assertForbidden();
        $this->postJson('/api/visitor/invitations', $this->invitation(['branch_id' => 2]), $this->headers())->assertForbidden();
        $this->postJson('/api/visitor/invitations', $this->invitation(['user' => ['id' => 3]]), $this->headers())
            ->assertCreated();
        $this->assertDatabaseHas('visitor_visits', ['registered_by' => 1, 'host_employee_id' => 2]);
        DB::table('employees')->where('id', 1)->update(['is_active' => false]);
        $this->getJson('/api/visitor/invitations', $this->headers())->assertForbidden();
    }

    public function test_pending_confirmation_is_created_and_can_be_cancelled(): void
    {
        DB::table('visitor_categories')->where('id', 1)->update(['requires_approval' => true]);
        $id = $this->postJson('/api/visitor/invitations', $this->invitation(), $this->headers())
            ->assertStatus(202)->assertJsonPath('data.status', 'pending_confirmation')->json('data.id');
        $this->postJson('/api/visitor/invitations/' . $id . '/cancel', ['reason' => 'Cancelled'], $this->headers())
            ->assertOk()->assertJsonPath('data.status', 'cancelled');
        $this->assertDatabaseHas('visitor_approvals', ['visitor_visit_id' => $id, 'status' => 'cancelled']);
    }

    public function test_only_assigned_host_can_approve_api_invitation(): void
    {
        DB::table('visitor_categories')->where('id', 1)->update(['requires_approval' => true]);
        $id = $this->postJson('/api/visitor/invitations', $this->invitation([
            'email' => 'guest@example.test',
        ]), $this->headers())
            ->assertStatus(202)
            ->assertJsonPath('data.status', 'pending_confirmation')
            ->json('data.id');

        $this->postJson('/api/visitor/invitations/' . $id . '/approval', [
            'decision' => 'approve',
        ], $this->headers())
            ->assertNotFound();

        $this->postJson('/api/visitor/invitations/' . $id . '/approval', [
            'decision' => 'approve',
            'notes' => 'Confirmed with the visitor.',
        ], $this->headers('host'))
            ->assertOk()
            ->assertJsonPath('message', 'Visit approved.')
            ->assertJsonPath('data.status', 'expected');

        $this->assertDatabaseHas('visitor_approvals', [
            'visitor_visit_id' => $id,
            'approver_employee_id' => 2,
            'status' => 'approved',
            'notes' => 'Confirmed with the visitor.',
        ]);
    }

    public function test_web_invitation_skips_approval_even_when_category_requires_it(): void
    {
        DB::table('visitor_categories')->where('id', 1)->update(['requires_approval' => true]);
        DB::table('visitor_purposes')->where('id', 1)->update(['requires_approval' => true]);

        $this->actingAs(User::findOrFail(1))
            ->post(route('visitor.store'), $this->invitation(['email' => 'visitor@example.test']))
            ->assertRedirect(route('visitor.index'));

        $visit = DB::table('visitor_visits')->where('registered_by', 1)->first();
        $this->assertNotNull($visit);
        $this->assertSame('expected', $visit->status);
        $this->assertDatabaseMissing('visitor_approvals', ['visitor_visit_id' => $visit->id]);
    }

    public function test_web_invitation_requires_email_but_allows_missing_phone(): void
    {
        $this->from(route('visitor.create'))
            ->actingAs(User::findOrFail(1))
            ->post(route('visitor.store'), $this->invitation())
            ->assertRedirect(route('visitor.create'))
            ->assertSessionHasErrors('email');

        $this->actingAs(User::findOrFail(1))
            ->post(route('visitor.store'), $this->invitation([
                'phone' => null,
                'email' => 'visitor@example.test',
            ]))
            ->assertRedirect(route('visitor.index'));

        $this->assertDatabaseHas('visitors', [
            'email' => 'visitor@example.test',
            'phone' => null,
        ]);
    }

    public function test_walk_in_registration_allows_optional_details_and_always_requires_host_approval(): void
    {
        DB::table('roles')->insert(['id' => 1, 'name' => 'admin']);
        DB::table('role_user')->insert(['role_id' => 1, 'user_id' => 1]);

        $this->actingAs(User::findOrFail(1))
            ->post(route('visitor.security-desk.walk-in'), [
                'full_name' => 'Walk-in Visitor',
                'email' => 'walkin@example.test',
                'branch_id' => 1,
                'host_employee_id' => 2,
                'bring_group' => '1',
                'group_members' => [
                    ['full_name' => 'Desk Guest One'],
                    ['full_name' => 'Desk Guest Two', 'email' => 'desk-guest@example.test'],
                ],
            ])
            ->assertRedirect(route('visitor.security-desk'))
            ->assertSessionHas('success', 'Walk-in registered and is waiting for host approval.');

        $visits = DB::table('visitor_visits')->where('registration_method', 'walk_in')->orderBy('id')->get();
        $visit = $visits->first();
        $this->assertNotNull($visit);
        $this->assertCount(3, $visits);
        $this->assertSame('pending_confirmation', $visit->status);
        $this->assertNull($visit->visitor_category_id);
        $this->assertNull($visit->visitor_purpose_id);
        $this->assertNull($visit->expected_departure_at);
        $this->assertNotNull($visit->visitor_visit_group_id);
        $this->assertSame(1, $visits->pluck('visitor_visit_group_id')->unique()->count());
        $this->assertDatabaseCount('visitor_approvals', 3);
        $this->assertDatabaseHas('visitors', ['full_name' => 'Desk Guest One']);
        $this->assertDatabaseHas('visitors', ['full_name' => 'Desk Guest Two']);
        $this->assertDatabaseHas('visitor_approvals', [
            'visitor_visit_id' => $visit->id,
            'approver_employee_id' => 2,
            'status' => 'pending',
        ]);
    }

    public function test_public_walk_in_registration_requires_host_approval_and_email_and_phone(): void
    {
        $this->get(route('visitor.public-walk-in'))
            ->assertOk()
            ->assertSee('Walk-in visitor registration')
            ->assertSee('name="email"', false)
            ->assertSee('name="phone"', false);

        $this->from(route('visitor.public-walk-in'))
            ->post(route('visitor.public-walk-in.store'), [
                'full_name' => 'Public Walk-in Visitor',
                'phone' => '08123456789',
                'email' => 'public-walkin@example.test',
                'branch_id' => 1,
                'host_employee_id' => 2,
                'bring_group' => '1',
                'group_members' => [
                    ['full_name' => 'Public Guest One', 'email' => 'public-walkin@example.test'],
                    ['full_name' => 'Public Guest Two', 'phone' => '08999999999'],
                ],
            ])
            ->assertOk()
            ->assertSee('Registration received')
            ->assertSee('2 companion registrations')
            ->assertSee('sent to the host for approval');

        $visits = DB::table('visitor_visits')->where('registration_method', 'public_walk_in')->orderBy('id')->get();
        $visit = $visits->first();
        $this->assertNotNull($visit);
        $this->assertCount(3, $visits);
        $this->assertNull($visit->registered_by);
        $this->assertSame('pending_confirmation', $visit->status);
        $this->assertNull($visit->visitor_category_id);
        $this->assertNull($visit->visitor_purpose_id);
        $this->assertNull($visit->expected_departure_at);
        $this->assertNotNull($visit->visitor_visit_group_id);
        $this->assertSame(1, $visits->pluck('visitor_visit_group_id')->unique()->count());
        $this->assertDatabaseHas('visitor_approvals', [
            'visitor_visit_id' => $visit->id,
            'approver_employee_id' => 2,
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('visitor_approvals', 3);
        $this->assertDatabaseHas('visitors', [
            'full_name' => 'Public Walk-in Visitor',
            'email' => 'public-walkin@example.test',
        ]);
        $this->assertDatabaseHas('visitors', [
            'full_name' => 'Public Guest One',
            'email' => 'public-walkin@example.test',
        ]);
        Notification::assertSentTo(
            User::findOrFail(2),
            VisitorVisitNotification::class
        );
    }

    public function test_pagination_links_preserve_filters(): void
    {
        $this->postJson('/api/visitor/invitations', $this->invitation(), $this->headers())->assertCreated();
        $this->postJson('/api/visitor/invitations', $this->invitation(['full_name' => 'Visitor Two', 'phone' => '0899999999']), $this->headers())
            ->assertCreated();

        foreach (['/api/visitor/invitations' => 'host', '/api/visitor/monitoring' => 'creator'] as $path => $user) {
            $response = $this->getJson($path . '?search=Visitor&status=expected&per_page=1', $this->headers($user))
                ->assertOk()->assertJsonPath('total', 2);
            $this->assertStringContainsString('status=expected', $response->json('next_page_url'));
            $this->getJson($response->json('next_page_url'), $this->headers($user))->assertOk()->assertJsonPath('current_page', 2);
        }
    }

    public function test_self_created_invitation_appears_only_in_host_invitations_list(): void
    {
        $id = $this->postJson('/api/visitor/invitations', $this->invitation(['host_employee_id' => 1]), $this->headers())
            ->assertCreated()->json('data.id');

        $this->getJson('/api/visitor/invitations', $this->headers())
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $id)
            ->assertJsonPath('data.0.can_cancel', true);
        $this->getJson('/api/visitor/monitoring', $this->headers())
            ->assertOk()->assertJsonPath('total', 0);
    }

    public function test_guest_and_host_emails_use_the_timeoff_branded_template(): void
    {
        $id = $this->postJson('/api/visitor/invitations', $this->invitation([
            'email' => 'guest@example.test',
        ]), $this->headers())->assertCreated()->json('data.id');
        $visit = VisitorVisit::with(['visitor', 'branch', 'host.user', 'purpose'])->findOrFail($id);

        $qrToken = str_repeat('a', 64);
        $guestMail = (new VisitorInvitationForGuest($visit, $qrToken))->toMail(new \stdClass());
        $this->assertSame('email-template.visitor-notification', $guestMail->view['html']);
        $guestHtml = $this->renderVisitorMail($guestMail);
        $this->assertStringContainsString('Visitor Invitation', $guestHtml);
        $this->assertStringContainsString('Visit date', $guestHtml);
        $this->assertStringContainsString('Meeting location', $guestHtml);
        $this->assertStringContainsString('Open your visitor QR code', $guestHtml);
        $this->assertStringContainsString(route('visitor.public-qr', ['token' => $qrToken]), $guestHtml);
        $this->assertStringContainsString('#f9f7f4', $guestHtml);

        VisitorVisit::whereKey($id)->update(['qr_token_hash' => hash('sha256', $qrToken)]);
        $this->get(route('visitor.public-qr', ['token' => $qrToken]))
            ->assertOk()
            ->assertSee('Your visitor QR code')
            ->assertSee('id="visitor-qr-code"', false)
            ->assertSee('html5-qrcode.min.js');
        $this->get(route('visitor.public-qr', ['token' => str_repeat('b', 64)]))->assertNotFound();

        $hostMail = (new VisitorVisitNotification($visit, 'invitation'))->toMail((object) ['name' => 'Host']);
        $this->assertSame('email-template.visitor-notification', $hostMail->view['html']);
        $hostHtml = $this->renderVisitorMail($hostMail);
        $this->assertStringContainsString('Visitor Invitation Created', $hostHtml);
        $this->assertStringContainsString(
            $visit->expected_arrival_at->format('d M Y H:i'),
            $hostHtml
        );
        $this->assertStringContainsString('Expected departure', $hostHtml);
        $this->assertStringContainsString(
            $visit->expected_departure_at->format('d M Y H:i'),
            $hostHtml
        );
        $this->assertStringContainsString('Open Visitor Dashboard', $hostHtml);
        $this->assertStringNotContainsString('Laravel Logo', $hostHtml);
    }

    private function renderVisitorMail(MailMessage $mail): string
    {
        return view($mail->view['html'], $mail->viewData + [
            'message' => new class {
                public function embedData($data, $name, $mime): string
                {
                    return 'cid:visitor-invitation';
                }
            },
        ])->render();
    }
}
