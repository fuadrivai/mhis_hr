<?php

namespace Tests\Feature;

use App\Services\AcademicYearService;
use App\Services\ApprovalEngine;
use App\Services\Implement\ApprovalRequestImplement;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApprovalByUserFilterTest extends TestCase
{
    private ApprovalRequestImplement $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');

        $pdo = DB::connection()->getPdo();
        $pdo->sqliteCreateFunction('JSON_UNQUOTE', fn ($value) => $value, 1);
        $pdo->sqliteCreateFunction('STR_TO_DATE', fn ($value, $format) => $value, 2);
        $pdo->sqliteCreateFunction('MONTH', fn ($value) => $value ? (int) substr($value, 5, 2) : null, 1);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token');
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('personal_id')->nullable();
            $table->unsignedBigInteger('employment_id')->nullable();
        });
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->timestamp('created_at')->nullable();
            $table->unsignedBigInteger('requester_employee_id')->nullable();
            $table->unsignedBigInteger('timeoff_id')->nullable();
        });
        Schema::create('approval_request_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_request_id');
            $table->json('payload');
        });
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_request_id');
            $table->unsignedBigInteger('approver_employee_id');
            $table->integer('step_order');
            $table->string('status');
        });

        DB::table('users')->insert(['id' => 10, 'name' => 'Approver']);
        DB::table('sessions')->insert(['user_id' => 10, 'token' => 'bearer test-token']);
        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => 10],
            ['id' => 2, 'user_id' => 20],
        ]);
        DB::table('approval_requests')->insert([
            ['id' => 1, 'status' => 'pending'],
            ['id' => 2, 'status' => 'pending'],
            ['id' => 3, 'status' => 'pending'],
            ['id' => 4, 'status' => 'pending'],
            ['id' => 5, 'status' => 'pending'],
        ]);
        DB::table('approval_request_data')->insert([
            ['approval_request_id' => 1, 'payload' => json_encode(['start_date' => '2025-12-30', 'end_date' => '2026-01-02', 'reason' => 'leave'])],
            ['approval_request_id' => 2, 'payload' => json_encode(['start_date' => '2026-02-10', 'end_date' => '2026-02-11', 'reason' => 'leave'])],
            ['approval_request_id' => 3, 'payload' => json_encode(['start_date' => '2026-01-10', 'reason' => 'leave'])],
            ['approval_request_id' => 4, 'payload' => json_encode(['start_date' => '2026-01-12', 'reason' => 'sick'])],
        ]);
        DB::table('approvals')->insert([
            ['id' => 1, 'approval_request_id' => 1, 'approver_employee_id' => 1, 'step_order' => 1, 'status' => 'pending'],
            ['id' => 2, 'approval_request_id' => 2, 'approver_employee_id' => 1, 'step_order' => 1, 'status' => 'approved'],
            ['id' => 3, 'approval_request_id' => 3, 'approver_employee_id' => 2, 'step_order' => 1, 'status' => 'pending'],
            ['id' => 4, 'approval_request_id' => 3, 'approver_employee_id' => 1, 'step_order' => 2, 'status' => 'pending'],
            ['id' => 5, 'approval_request_id' => 4, 'approver_employee_id' => 2, 'step_order' => 1, 'status' => 'approved'],
            ['id' => 6, 'approval_request_id' => 4, 'approver_employee_id' => 1, 'step_order' => 2, 'status' => 'pending'],
            ['id' => 7, 'approval_request_id' => 5, 'approver_employee_id' => 1, 'step_order' => 1, 'status' => 'pending'],
        ]);

        $this->service = new ApprovalRequestImplement(new ApprovalEngine(), $this->createStub(AcademicYearService::class));
    }

    private function approvalIds(array $filters = []): array
    {
        return $this->service->getApprovalByUser(array_merge(['user' => ['id' => 10]], $filters))
            ->pluck('id')->sort()->values()->all();
    }

    public function test_status_filter_works_without_month_or_year_and_uses_approver_step_status(): void
    {
        $this->assertSame([1, 6, 7], $this->approvalIds(['status' => 'pending']));
        $this->assertSame([2], $this->approvalIds(['status' => 'approved']));
        $this->assertSame([], $this->approvalIds(['status' => 'rejected']));
        $this->assertSame([1, 2, 6, 7], $this->approvalIds());
        $this->assertSame([1, 2, 6, 7], $this->approvalIds(['status' => 'all', 'month' => '', 'year' => '']));
    }

    public function test_status_can_be_combined_with_date_and_dynamic_payload_filters(): void
    {
        $this->assertSame([1], $this->approvalIds(['status' => 'pending', 'month' => 1, 'year' => 2026, 'reason' => 'leave']));
        $this->assertSame([6], $this->approvalIds(['status' => 'pending', 'month' => 1, 'year' => 2026, 'reason' => 'sick']));
        $this->assertSame([2], $this->approvalIds(['status' => 'approved', 'year' => 2026]));
        $this->assertSame([1, 6], $this->approvalIds(['status' => 'pending', 'month' => 1]));
    }

    public function test_pending_next_step_becomes_visible_after_previous_step_is_approved(): void
    {
        DB::table('approvals')->where('id', 3)->update(['status' => 'approved']);

        $this->assertSame([1, 4, 6, 7], $this->approvalIds(['status' => 'pending']));
    }

    public function test_api_accepts_status_without_date_parameters(): void
    {
        $this->getJson('/api/time/request/approval?status=approved', ['Authorization' => 'bearer test-token'])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', 2)
            ->assertJsonPath('0.status', 'approved');
    }

    public function test_approvals_are_ordered_by_timeoff_start_date_descending(): void
    {
        DB::table('approval_requests')->where('id', 1)->update(['created_at' => '2026-03-01 10:00:00']);
        DB::table('approval_requests')->where('id', 4)->update(['created_at' => '2026-01-01 10:00:00']);
        DB::table('approval_request_data')->insert([
            'approval_request_id' => 5,
            'payload' => json_encode(['start_date' => null, 'date' => '2026-01-12']),
        ]);
        DB::table('approval_requests')->insert(['id' => 6, 'status' => 'pending']);
        DB::table('approvals')->insert([
            'id' => 8, 'approval_request_id' => 6, 'approver_employee_id' => 1,
            'step_order' => 1, 'status' => 'pending',
        ]);

        $this->getJson('/api/time/request/approval?status=pending', ['Authorization' => 'bearer test-token'])
            ->assertOk()
            ->assertJsonCount(4)
            ->assertJsonPath('0.id', 7)
            ->assertJsonPath('1.id', 6)
            ->assertJsonPath('2.id', 1)
            ->assertJsonPath('3.id', 8);
    }

    public function test_next_approval_follows_user_order_and_ignores_list_filters(): void
    {
        DB::table('approval_request_data')->insert([
            'approval_request_id' => 5,
            'payload' => json_encode(['date' => '2026-03-01']),
        ]);

        $this->getJson('/api/time/request/approval/7/next?month=12&year=1999&status=approved', [
            'Authorization' => 'bearer test-token',
        ])->assertOk()
            ->assertJsonPath('data.id', 6)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.approval_request.id', 4)
            ->assertJsonPath('data.approval_request.data.payload.start_date', '2026-01-12');

        $this->getJson('/api/time/request/approval/6/next', ['Authorization' => 'bearer test-token'])
            ->assertOk()->assertJsonPath('data.id', 1);

        DB::table('approvals')->where('id', 3)->update(['status' => 'approved']);
        $this->getJson('/api/time/request/approval/6/next', ['Authorization' => 'bearer test-token'])
            ->assertOk()->assertJsonPath('data.id', 4);
    }

    public function test_next_wraps_and_works_after_current_approval_is_processed(): void
    {
        DB::table('approval_request_data')->insert([
            'approval_request_id' => 5,
            'payload' => json_encode(['date' => '2026-03-01']),
        ]);
        DB::table('approvals')->where('id', 1)->update(['status' => 'approved']);
        DB::table('approval_requests')->where('id', 1)->update(['status' => 'approved']);

        $this->getJson('/api/time/request/approval/1/next', ['Authorization' => 'bearer test-token'])
            ->assertOk()->assertJsonPath('data.id', 7);
    }

    public function test_next_returns_null_when_no_other_eligible_request_exists(): void
    {
        DB::table('approvals')->where('id', 6)->update(['status' => 'approved']);
        DB::table('approval_requests')->where('id', 5)->update(['status' => 'cancelled']);
        DB::table('approvals')->where('id', 1)->update(['status' => 'approved']);
        DB::table('approvals')->insert([
            'id' => 8, 'approval_request_id' => 1, 'approver_employee_id' => 1,
            'step_order' => 2, 'status' => 'pending',
        ]);

        $this->getJson('/api/time/request/approval/1/next', ['Authorization' => 'bearer test-token'])
            ->assertOk()->assertExactJson(['data' => null]);
    }

    public function test_next_rejects_another_users_approval_or_a_future_step(): void
    {
        $headers = ['Authorization' => 'bearer test-token'];

        $this->getJson('/api/time/request/approval/3/next', $headers)->assertNotFound();
        $this->getJson('/api/time/request/approval/4/next', $headers)->assertNotFound();
        $this->getJson('/api/time/request/approval/999/next', $headers)->assertNotFound();
        $this->getJson('/api/time/request/approval/1/next')->assertForbidden();
    }
}
