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
        Schema::create('employments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('job_level_id')->nullable();
            $table->unsignedBigInteger('job_position_id')->nullable();
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
        DB::table('employments')->insert(['id' => 1, 'branch_id' => 1]);
        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => 1, 'employment_id' => 1, 'is_active' => true],
            ['id' => 2, 'user_id' => 2, 'employment_id' => 1, 'is_active' => true],
            ['id' => 3, 'user_id' => 3, 'employment_id' => 1, 'is_active' => true],
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
            'expected_arrival_date' => $date->format('d F Y'),
            'expected_arrival_time' => '09:00',
            'expected_departure_date' => $date->format('d F Y'),
            'expected_departure_time' => '10:00',
        ], $overrides);
    }

    public function test_create_invitation_for_another_host_and_view_details_as_creator(): void
    {
        $response = $this->postJson('/api/visitor/invitations', $this->invitation(), $this->headers())
            ->assertCreated()->assertJsonPath('data.host_employee_id', 2)
            ->assertJsonStructure(['data' => ['id', 'visit_code', 'status']]);
        $id = $response->json('data.id');

        $this->assertDatabaseHas('visitor_visits', ['id' => $id, 'registered_by' => 1, 'host_employee_id' => 2]);
        $this->getJson('/api/visitor/invitations/' . $id, $this->headers())
            ->assertOk()->assertJsonPath('data.visitor.full_name', 'Visitor One')
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

    public function test_self_created_invitation_appears_in_both_lists(): void
    {
        $id = $this->postJson('/api/visitor/invitations', $this->invitation(['host_employee_id' => 1]), $this->headers())
            ->assertCreated()->json('data.id');

        foreach (['/api/visitor/invitations', '/api/visitor/monitoring'] as $path) {
            $this->getJson($path, $this->headers())
                ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $id)
                ->assertJsonPath('data.0.can_cancel', true);
        }
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
