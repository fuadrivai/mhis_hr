<?php

namespace Tests\Feature;

use App\Models\VisitorVisit;
use App\Notifications\VisitorInvitationForGuest;
use App\Notifications\VisitorVisitNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
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

        $guestMail = (new VisitorInvitationForGuest($visit, 'qr-token'))->toMail(new \stdClass());
        $this->assertSame('email-template.visitor-notification', $guestMail->view['html']);
        $guestHtml = $this->renderVisitorMail($guestMail);
        $this->assertStringContainsString('Visitor Invitation', $guestHtml);
        $this->assertStringContainsString('Visit date', $guestHtml);
        $this->assertStringContainsString('Meeting location', $guestHtml);
        $this->assertStringContainsString('Visitor invitation QR code', $guestHtml);
        $this->assertStringContainsString('#f9f7f4', $guestHtml);

        $hostMail = (new VisitorVisitNotification($visit, 'invitation'))->toMail((object) ['name' => 'Host']);
        $this->assertSame('email-template.visitor-notification', $hostMail->view['html']);
        $hostHtml = $this->renderVisitorMail($hostMail);
        $this->assertStringContainsString('Visitor Invitation Created', $hostHtml);
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
