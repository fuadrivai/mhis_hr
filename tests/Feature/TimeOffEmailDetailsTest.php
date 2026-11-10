<?php

namespace Tests\Feature;

use App\Mail\TimeoffMail;
use App\Http\Controllers\ApprovalRequestController;
use App\Http\Controllers\Api\ApprovalRequestApiController;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestData;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\TimeOff;
use App\Models\User;
use App\Services\AcademicYearService;
use App\Services\ApprovalEngine;
use App\Services\ApprovalRequestService;
use App\Services\EmployeeService;
use App\Services\Implement\ApprovalRequestImplement;
use App\Services\TimeOffEmailDetails;
use App\Services\TimeOffService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Yajra\DataTables\Utilities\Request as DataTablesRequest;

class TimeOffEmailDetailsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'app.key' => 'base64:' . base64_encode(str_repeat('a', 32)),
        ]);
        DB::purge('sqlite');

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('personal_id');
        });
        Schema::create('personals', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('email');
        });
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('count_detail');
        });
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('schedule_id');
            $table->date('effective_start_date');
            $table->date('effective_end_date')->nullable();
        });
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('holiday')->default(false);
        });
        Schema::create('schedule_details', function (Blueprint $table) {
            $table->id();
            $table->integer('schedule_id');
            $table->integer('shift_id');
            $table->integer('number');
        });
        Schema::create('employee_shift_overrides', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('shift_id');
            $table->date('date');
        });
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->integer('approval_request_id');
            $table->integer('approver_employee_id');
            $table->integer('step_order');
            $table->string('status');
        });
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('requester_employee_id');
            $table->integer('timeoff_id');
            $table->string('status');
        });
        Schema::create('approval_request_data', function (Blueprint $table) {
            $table->id();
            $table->integer('approval_request_id');
            $table->json('payload');
        });
        Schema::create('timeoffs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('schema')->nullable();
        });

        DB::table('personals')->insert([
            ['id' => 1, 'fullname' => 'Requester', 'email' => 'requester@example.test'],
            ['id' => 2, 'fullname' => 'Approver', 'email' => 'approver@example.test'],
        ]);
        DB::table('employees')->insert([
            ['id' => 1, 'user_id' => 1, 'personal_id' => 1],
            ['id' => 2, 'user_id' => 2, 'personal_id' => 2],
        ]);
        DB::table('schedules')->insert(['id' => 1, 'count_detail' => 4]);
        DB::table('employee_schedules')->insert([
            'employee_id' => 1, 'schedule_id' => 1, 'effective_start_date' => '2026-11-06',
        ]);
        DB::table('shifts')->insert([
            ['id' => 1, 'name' => 'Regular', 'holiday' => false],
            ['id' => 2, 'name' => 'Day Off', 'holiday' => false],
            ['id' => 3, 'name' => 'Holiday', 'holiday' => true],
        ]);
        DB::table('schedule_details')->insert([
            ['schedule_id' => 1, 'shift_id' => 1, 'number' => 1],
            ['schedule_id' => 1, 'shift_id' => 2, 'number' => 2],
            ['schedule_id' => 1, 'shift_id' => 2, 'number' => 3],
            ['schedule_id' => 1, 'shift_id' => 1, 'number' => 4],
        ]);
        DB::table('approvals')->insert([
            'approval_request_id' => 5, 'approver_employee_id' => 2, 'step_order' => 1, 'status' => 'pending',
        ]);
    }

    public function test_schedule_rotation_and_overrides_determine_day_duration(): void
    {
        $details = new TimeOffEmailDetails();
        $employee = Employee::findOrFail(1);
        $payload = ['start_date' => '2026-11-06', 'end_date' => '2026-11-09'];

        $this->assertSame('2 days', $details->build($employee, $payload)['timeoff_scheduled_days']);

        DB::table('employee_shift_overrides')->insert([
            'employee_id' => 1, 'shift_id' => 1, 'date' => '2026-11-07',
        ]);
        $this->assertSame('3 days', $details->build($employee, $payload)['timeoff_scheduled_days']);
        DB::table('employee_shift_overrides')->insert([
            'employee_id' => 1, 'shift_id' => 3, 'date' => '2026-11-09',
        ]);
        $this->assertSame('2 days', $details->build($employee, $payload)['timeoff_scheduled_days']);
        $this->assertSame('0 days', $details->build($employee, ['date' => '2026-11-08'])['timeoff_scheduled_days']);
        $this->assertSame('0 days', $details->build($employee, ['start_date' => '2026-11-05'])['timeoff_scheduled_days']);
    }

    public function test_schedule_effective_dates_and_newer_assignment_are_respected(): void
    {
        DB::table('schedules')->insert(['id' => 2, 'count_detail' => 1]);
        DB::table('schedule_details')->insert(['schedule_id' => 2, 'shift_id' => 2, 'number' => 1]);
        DB::table('employee_schedules')->insert([
            'employee_id' => 1, 'schedule_id' => 2, 'effective_start_date' => '2026-11-09',
            'effective_end_date' => '2026-11-09',
        ]);
        $details = (new TimeOffEmailDetails())->build(Employee::findOrFail(1), [
            'start_date' => '2026-11-06', 'end_date' => '2026-11-10',
        ]);
        $this->assertSame('2 days', $details['timeoff_scheduled_days']);
    }

    public function test_request_and_approval_datatables_return_scheduled_days(): void
    {
        DB::table('timeoffs')->insert(['id' => 1, 'name' => 'Sick Leave', 'schema' => json_encode([
            ['name' => 'start_time', 'type' => 'time', 'show_if' => ['field' => 'type', 'value' => 'hourly']],
            ['name' => 'end_time', 'type' => 'time', 'show_if' => ['field' => 'type', 'value' => 'hourly']],
        ])]);
        DB::table('approval_requests')->insert([
            ['id' => 5, 'requester_employee_id' => 1, 'timeoff_id' => 1, 'status' => 'pending'],
            ['id' => 6, 'requester_employee_id' => 1, 'timeoff_id' => 1, 'status' => 'pending'],
            ['id' => 7, 'requester_employee_id' => 1, 'timeoff_id' => 1, 'status' => 'pending'],
            ['id' => 8, 'requester_employee_id' => 1, 'timeoff_id' => 1, 'status' => 'pending'],
            ['id' => 9, 'requester_employee_id' => 1, 'timeoff_id' => 1, 'status' => 'pending'],
        ]);
        DB::table('approval_request_data')->insert([
            ['approval_request_id' => 5, 'payload' => json_encode(['start_date' => '2026-11-06', 'end_date' => '2026-11-09'])],
            ['approval_request_id' => 6, 'payload' => json_encode(['start_date' => '2026-11-07', 'end_date' => '2026-11-08', 'start_time' => '09:15', 'end_time' => '11:45'])],
            ['approval_request_id' => 7, 'payload' => json_encode(['start_date' => '2026-11-06'])],
            ['approval_request_id' => 8, 'payload' => json_encode(['start_date' => '2026-11-09', 'end_date' => '2026-11-06'])],
            ['approval_request_id' => 9, 'payload' => json_encode(['start_date' => 'not-a-date', 'end_date' => '2026-11-06'])],
        ]);
        DB::table('approvals')->insert([
            ['approval_request_id' => 6, 'approver_employee_id' => 2, 'step_order' => 1, 'status' => 'pending'],
            ['approval_request_id' => 7, 'approver_employee_id' => 2, 'step_order' => 1, 'status' => 'pending'],
            ['approval_request_id' => 8, 'approver_employee_id' => 2, 'step_order' => 1, 'status' => 'pending'],
            ['approval_request_id' => 9, 'approver_employee_id' => 2, 'step_order' => 1, 'status' => 'pending'],
        ]);
        $service = $this->createStub(ApprovalRequestService::class);
        $service->method('getDataTable')->willReturn(ApprovalRequest::with(['data', 'type', 'requester.personal']));
        $controller = new ApprovalRequestController(
            $this->createStub(EmployeeService::class),
            $this->createStub(TimeOffService::class),
            $service
        );
        $request = Request::create('/time/request/datatable', 'GET', ['draw' => 1, 'start' => 0, 'length' => 25], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);
        $this->app->instance('request', $request);
        $user = new User();
        $user->id = 2;
        Auth::setUser($user);

        foreach (['dataTable', 'approvalDataTable'] as $method) {
            $response = $controller->$method(new DataTablesRequest());
            $rows = collect($response->getData(true)['data'])->keyBy('id');
            $firstId = $method === 'dataTable' ? 5 : 1;
            $this->assertSame(2, $rows[$firstId]['scheduled_days']);
            $this->assertSame(0, $rows[$firstId + 1]['scheduled_days']);
            $this->assertSame('09:15', $rows[$firstId + 1]['start_time']);
            $this->assertSame('11:45', $rows[$firstId + 1]['end_time']);
            $this->assertSame('26h 30m', $rows[$firstId + 1]['time_duration']);
            $this->assertSame(1, $rows[$firstId + 2]['scheduled_days']);
            $this->assertTrue($rows[$firstId + 2]['has_time_fields']);
            $this->assertSame('invalid', $rows[$firstId + 3]['scheduled_days']);
            $this->assertSame('invalid', $rows[$firstId + 4]['scheduled_days']);
            $this->assertSame('2026-11-06', $rows[$firstId]['start_date']);
            $this->assertSame('2026-11-09', $rows[$firstId]['end_date']);
            $requester = $method === 'dataTable'
                ? $rows[$firstId]['requester']
                : $rows[$firstId]['approval_request']['requester'];
            $this->assertArrayNotHasKey('schedules', $requester);
            $this->assertArrayNotHasKey('shift_overrides', $requester);
        }
    }

    public function test_both_submission_controllers_preserve_all_dynamic_fields(): void
    {
        DB::table('timeoffs')->insert(['id' => 1, 'name' => 'Come Late']);
        $dynamicFields = [
            'start_date' => '2026-11-06',
            'type' => 'hourly',
            'start_time' => '09:15',
            'end_time' => '11:45',
            'other_field' => 'Additional information',
            'choices' => ['one', 'two'],
        ];
        foreach (['web', 'api'] as $channel) {
            $service = $this->createMock(ApprovalRequestService::class);
            $service->expects($this->once())->method('post')
                ->with($this->callback(function ($data) use ($dynamicFields) {
                    return $data['dynamic_fields'] === $dynamicFields;
                }))
                ->willReturn(new ApprovalRequest());
            $request = Request::create('/', 'POST', [
                'requester_employee_id' => 1,
                'timeoff_id' => 1,
                'dynamic_fields' => $dynamicFields,
            ]);
            $request->setLaravelSession($this->app['session']->driver());
            $this->app->instance('request', $request);

            if ($channel === 'web') {
                $controller = new ApprovalRequestController($this->createStub(EmployeeService::class), $this->createStub(TimeOffService::class), $service);
            } else {
                $controller = new ApprovalRequestApiController($service);
            }
            $controller->store($request);
        }
    }

    public function test_request_details_display_time_and_day_durations_and_missing_times(): void
    {
        $schemaLabels = collect(['start_date' => 'Start date', 'start_time' => 'Start Time', 'end_time' => 'End Time']);
        $timeFields = collect([
            ['name' => 'start_time', 'label' => 'Start Time'],
            ['name' => 'end_time', 'label' => 'End Time'],
        ]);
        $payload = ['start_date' => '2026-11-06', 'start_time' => '09:15', 'end_time' => '11:45'];
        $html = view('approval.request.payload-details', [
            'payload' => $payload, 'orderedPayload' => collect($payload), 'schemaLabels' => $schemaLabels,
            'timeFields' => $timeFields, 'hasTimeFields' => true, 'timeDuration' => '2h 30m', 'scheduledDays' => 1,
        ])->render();
        $this->assertStringContainsString('09:15', $html);
        $this->assertStringContainsString('11:45', $html);
        $this->assertStringContainsString('2h 30m', $html);

        $payload = ['start_date' => '2026-11-06'];
        $html = view('approval.request.payload-details', [
            'payload' => $payload, 'orderedPayload' => collect($payload), 'schemaLabels' => $schemaLabels,
            'timeFields' => $timeFields, 'hasTimeFields' => true, 'timeDuration' => null, 'scheduledDays' => 1,
        ])->render();
        $this->assertStringContainsString('Start Time', $html);
        $this->assertStringContainsString('End Time', $html);
        $this->assertSame(2, substr_count($html, 'Not provided'));
        $this->assertStringContainsString('Not available', $html);

        $payload = ['start_date' => '2026-11-06', 'end_date' => '2026-11-09'];
        $html = view('approval.request.payload-details', [
            'payload' => $payload, 'orderedPayload' => collect($payload), 'schemaLabels' => $schemaLabels,
            'timeFields' => collect(), 'hasTimeFields' => false, 'timeDuration' => null, 'scheduledDays' => 2,
        ])->render();
        $this->assertStringContainsString('2 day(s)', $html);
    }

    public function test_preview_displays_missing_hourly_fields_without_attachments(): void
    {
        $employee = Employee::findOrFail(1);
        $employee->load('personal');
        $employment = new Employment();
        $employment->setRelation('job_position', null);
        $employment->setRelation('branch', null);
        $employee->setRelation('employment', $employment);
        $request = new ApprovalRequest(['status' => 'pending']);
        $request->id = 1084;
        $request->setRelation('requester', $employee);
        $request->setRelation('data', new ApprovalRequestData(['payload' => ['start_date' => '2026-11-06']]));
        $schema = [
            ['name' => 'start_time', 'label' => 'Start Time', 'show_if' => ['field' => 'type', 'value' => 'hourly']],
            ['name' => 'end_time', 'label' => 'End Time', 'show_if' => ['field' => 'type', 'value' => 'hourly']],
        ];
        $request->setRelation('type', new TimeOff(['name' => 'Come Late', 'schema' => $schema]));
        $request->setRelation('attachments', collect());
        $request->setRelation('approvals', collect());
        $request->setRelation('histories', collect());
        $user = $this->getMockBuilder(User::class)->onlyMethods(['hasRole'])->getMock();
        $user->expects($this->once())->method('hasRole')->with('admin')->willReturn(false);
        $user->setRelation('roles', collect());
        Auth::setUser($user);

        $template = file_get_contents(resource_path('views/approval/request/preview.blade.php'));
        $template = str_replace("@extends('layouts.main-layout')", '', $template) . "\n@yield('content-child')";
        $html = \Illuminate\Support\Facades\Blade::render($template, [
            'approvalRequest' => $request,
            'scheduledDays' => 1,
            'timeDuration' => null,
            'timeFields' => collect($schema),
            'nextRequest' => null,
        ]);
        $this->assertStringContainsString('No attachment provided', $html);
        $this->assertStringContainsString('Start Time', $html);
        $this->assertStringContainsString('End Time', $html);
        $this->assertStringContainsString('Not available', $html);
    }

    public function test_new_request_rejects_reversed_date_range(): void
    {
        $service = new ApprovalRequestImplement(new ApprovalEngine(), $this->createStub(AcademicYearService::class));
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->expectExceptionMessage('Tanggal akhir tidak boleh sebelum tanggal mulai.');
        (new \ReflectionMethod($service, 'ensureNoOverlappingTimeOff'))->invoke($service, Employee::findOrFail(1), [
            'start_date' => '2026-11-09',
            'end_date' => '2026-11-06',
        ]);
    }

    public function test_both_emails_include_dates_times_and_duration(): void
    {
        Mail::fake();
        $request = new ApprovalRequest(['status' => 'approved', 'note' => 'Sick']);
        $request->id = 5;
        $request->setRelation('requester', Employee::findOrFail(1));
        $request->setRelation('type', new TimeOff(['name' => 'Sick Leave']));
        $request->setRelation('data', new ApprovalRequestData(['payload' => [
            'start_date' => '2026-11-06', 'end_date' => '2026-11-09',
        ]]));
        $service = new ApprovalRequestImplement(new ApprovalEngine(), $this->createStub(AcademicYearService::class));

        foreach (['_sendEmail', '_sendEmailToRequester'] as $method) {
            (new \ReflectionMethod($service, $method))->invoke($service, $request);
        }

        Mail::assertSent(TimeoffMail::class, 2);
        Mail::assertSent(TimeoffMail::class, function (TimeoffMail $mail) {
            $html = $mail->render();
            return str_contains($html, 'Fri, Nov 06, 2026')
                && str_contains($html, 'Mon, Nov 09, 2026')
                && str_contains($html, '2 days');
        });
        foreach (Mail::sent(TimeoffMail::class) as $mail) {
            $html = $mail->render();
            $this->assertStringContainsString('Fri, Nov 06, 2026', $html);
            $this->assertStringContainsString('Mon, Nov 09, 2026', $html);
            $this->assertStringContainsString('2 days', $html);
        }

        $request->setRelation('data', new ApprovalRequestData(['payload' => [
            'start_date' => '2026-11-06', 'end_date' => '2026-11-06',
            'start_time' => '09:15', 'end_time' => '11:45',
        ]]));
        (new \ReflectionMethod($service, '_sendEmail'))->invoke($service, $request);
        $html = Mail::sent(TimeoffMail::class)->last()->render();
        $this->assertStringContainsString('09:15', $html);
        $this->assertStringContainsString('11:45', $html);
        $this->assertStringContainsString('150 minutes', $html);
        (new \ReflectionMethod($service, '_sendEmailToRequester'))->invoke($service, $request);
        $html = Mail::sent(TimeoffMail::class)->last()->render();
        $this->assertStringContainsString('09:15', $html);
        $this->assertStringContainsString('11:45', $html);
        $this->assertStringContainsString('150 minutes', $html);
    }

    public function test_email_renders_days_hourly_minutes_and_full_day_type(): void
    {
        $request = new ApprovalRequest(['status' => 'approved', 'note' => '']);
        $request->id = 5;
        $request->setRelation('requester', Employee::findOrFail(1));
        $request->setRelation('type', new TimeOff(['name' => 'Leave']));
        $service = new ApprovalRequestImplement(new ApprovalEngine(), $this->createStub(AcademicYearService::class));

        $cases = [
            [
                'payload' => ['start_date' => '2026-11-06', 'end_date' => '2026-11-09'],
                'contains' => ['Fri, Nov 06, 2026', 'Mon, Nov 09, 2026', 'Day(s)', '2 days'],
                'absent' => ['Duration', '>Type<'],
            ],
            [
                'payload' => ['start_date' => '2026-11-06', 'end_date' => '2026-11-06'],
                'contains' => ['Fri, Nov 06, 2026', 'Day(s)', '1 day'],
                'absent' => ['Duration', '>Type<'],
                'date_count' => 1,
            ],
            [
                'payload' => ['start_date' => '2026-11-06', 'type' => 'hourly', 'start_time' => '07:00', 'end_time' => '09:00'],
                'contains' => ['Fri, Nov 06, 2026', '>Type<', 'hourly', '>Time<', '07:00', '09:00', 'Duration', '120 minutes'],
                'absent' => ['Day(s)'],
            ],
            [
                'payload' => ['start_date' => '2026-11-06', 'type' => 'fullday'],
                'contains' => ['Fri, Nov 06, 2026', '>Type<', 'fullday'],
                'absent' => ['Day(s)', 'Duration', '>Time<'],
            ],
        ];

        foreach ($cases as $case) {
            Mail::fake();
            $request->setRelation('data', new ApprovalRequestData(['payload' => $case['payload']]));
            foreach (['_sendEmail', '_sendEmailToRequester'] as $method) {
                (new \ReflectionMethod($service, $method))->invoke($service, $request);
            }
            Mail::assertSent(TimeoffMail::class, 2);
            foreach (Mail::sent(TimeoffMail::class) as $mail) {
                $html = $mail->render();
                foreach ($case['contains'] as $expected) {
                    $this->assertStringContainsString($expected, $html);
                }
                foreach ($case['absent'] as $unexpected) {
                    $this->assertStringNotContainsString($unexpected, $html);
                }
                if (isset($case['date_count'])) {
                    $this->assertSame($case['date_count'], substr_count($html, 'Fri, Nov 06, 2026'));
                }
            }
        }
    }

    public function test_time_only_and_incomplete_fields(): void
    {
        $details = new TimeOffEmailDetails();
        $employee = Employee::findOrFail(1);
        $this->assertSame('65 minutes', $details->build($employee, [
            'start_time' => '09:00:00', 'end_time' => '10:05:00',
        ])['timeoff_duration']);
        $this->assertNull($details->build($employee, ['start_time' => '09:00'])['timeoff_duration']);
        $multiDay = $details->build($employee, [
            'start_date' => '2026-11-06', 'end_date' => '2026-11-09',
            'start_time' => '09:00', 'end_time' => '10:00',
        ]);
        $this->assertNull($multiDay['timeoff_scheduled_days']);
        $this->assertSame('4380 minutes', $multiDay['timeoff_duration']);
    }
}
