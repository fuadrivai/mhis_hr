<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Cutoff;
use App\Models\Employee;
use App\Models\EmployeeShiftOverride;
use App\Models\ApprovalRequest;
use App\Models\Personal;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\JobLevel;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    public function attendance()
    {
        return view('report.attendance', [
            'title' => 'Attendance Report',
            'branches' => Branch::orderBy('name')->get(),
            'organizations' => Organization::orderBy('name')->get(),
            'levels' => JobLevel::orderBy('name')->get(),
            'positions' => Position::orderBy('name')->get(),
        ]);
    }

    public function monthly(Request $request)
    {
        $month = Carbon::createFromFormat('Y-m', $request->input('month', now()->format('Y-m')))->startOfMonth();
        [$startDate, $endDate, $cutoff] = $this->reportPeriod($month);
        $employees = $this->reportEmployees($request)->get();
        $employeeIds = $employees->pluck('id');
        $attendances = Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn ($attendance) => $attendance->employee_id . '|' . $attendance->date->toDateString());
        $overrides = EmployeeShiftOverride::with('shift')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn ($override) => $override->employee_id . '|' . $override->date->toDateString());
        $timeoffs = $this->approvedTimeoffs($employees, $startDate, $endDate);
        $dates = collect();
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates->push($date->copy());
        }

        return response()->json([
            'month' => $month->format('F Y'),
            'cutoff_day' => (int) $cutoff->cutoff_day,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'dates' => $dates->map(fn ($date) => [
                'date' => $date->toDateString(),
                'day' => $date->format('j'),
                'weekday' => $date->format('D'),
            ])->values(),
            'employees' => $employees->map(function ($employee) use ($dates, $attendances, $overrides, $timeoffs) {
                $lateMinutes = 0;
                $absent = 0;
                $timeoff = 0;
                $cells = [];

                foreach ($dates as $date) {
                    $key = $employee->id . '|' . $date->toDateString();
                    $shift = $this->resolveReportShift($employee, $date, $overrides->get($key));
                    $attendance = $attendances->get($key);
                    $leave = $timeoffs[$key] ?? null;
                    $cell = $this->buildReportCell($attendance, $shift, $leave, $date);
                    $lateMinutes += $cell['late_minutes'];
                    $absent += $cell['status'] === 'A' ? 1 : 0;
                    $timeoff += $cell['status'] === 'TO' ? 1 : 0;
                    $cells[] = $cell;
                }

                return [
                    'id' => $employee->id,
                    'name' => optional($employee->personal)->fullname ?? 'Unknown Employee',
                    'late' => $this->formatMinutes($lateMinutes),
                    'absent' => $absent,
                    'timeoff' => $timeoff,
                    'cells' => $cells,
                ];
            })->values(),
        ]);
    }

    private function reportPeriod(Carbon $month): array
    {
        $cutoff = Cutoff::where('is_active', true)->firstOrFail();
        $endDate = $month->copy()->day((int) $cutoff->cutoff_day);
        $startDate = $endDate->copy()->subMonth()->addDay();

        return [$startDate, $endDate, $cutoff];
    }

    public function monthlyDetail(Request $request, Employee $employee)
    {
        $this->authorizeReportEmployee($employee);
        $date = Carbon::createFromFormat('Y-m-d', $request->input('date'))->startOfDay();
        $attendance = $employee->attendances()->whereDate('date', $date)->first();
        $override = $employee->shiftOverrides()->with('shift')->whereDate('date', $date)->first();
        $shift = $this->resolveReportShift($employee, $date, $override);
        $timeoffs = $this->approvedTimeoffs(collect([$employee]), $date->copy()->startOfMonth(), $date->copy()->endOfMonth());
        $cell = $this->buildReportCell($attendance, $shift, $timeoffs[$employee->id . '|' . $date->toDateString()] ?? null, $date);

        return response()->json([
            'employee' => optional($employee->personal)->fullname ?? 'Unknown Employee',
            'date' => $date->format('d F Y'),
            'shift' => $shift ? $shift->name : '-',
            'schedule_in' => $shift ? $shift->schedule_in : '-',
            'schedule_out' => $shift ? $shift->schedule_out : '-',
            'clock_in' => optional(optional($attendance)->check_in)->format('H:i') ?? '-',
            'clock_out' => optional(optional($attendance)->check_out)->format('H:i') ?? '-',
            'status' => $cell['label'],
            'timeoff_type' => $cell['timeoff_type'],
            'attendance_id' => optional($attendance)->id,
        ]);
    }

    public function monthlyLogs(Request $request, Employee $employee)
    {
        $this->authorizeReportEmployee($employee);
        $date = Carbon::createFromFormat('Y-m-d', $request->input('date'))->toDateString();
        return response()->json(AttendanceLog::where('employee_id', $employee->id)
            ->whereDate('clock_date', $date)
            ->orderBy('clock_datetime')
            ->get(['id', 'type', 'time', 'clock_datetime', 'has_location', 'latitude', 'longitude', 'radius']));
    }

    private function reportEmployees(?Request $request = null)
    {
        $query = Employee::with([
            'personal',
            'employment',
            'schedules.schedule.details.shift',
            'shiftOverrides.shift',
        ])->where('is_active', true)->orderBy(Personal::select('fullname')->whereColumn('personals.id', 'employees.personal_id'));
        foreach ([
            'branch' => 'branch_id',
            'organization' => 'organization_id',
            'level' => 'job_level_id',
            'position' => 'job_position_id',
        ] as $filter => $column) {
            if ($request && $request->filled($filter) && $request->input($filter) !== 'all') {
                $query->whereHas('employment', function ($employmentQuery) use ($request, $filter, $column) {
                    $employmentQuery->where($column, $request->input($filter));
                });
            }
        }
        $user = auth()->user();
        if ($user && $user->roles->contains('id', 3)) {
            $employee = $user->employee;
            if ($employee && $employee->employment) {
                $query->whereHas('employment', function ($q) use ($employee) {
                    $q->where('branch_id', $employee->employment->branch_id)
                        ->where('organization_id', $employee->employment->organization_id);
                });
            } else {
                $query->whereKey(0);
            }
        }
        return $query;
    }

    private function authorizeReportEmployee(Employee $employee): void
    {
        if (!$this->reportEmployees()->whereKey($employee->id)->exists()) {
            abort(403);
        }
    }

    private function resolveReportShift(Employee $employee, Carbon $date, $override = null)
    {
        if ($override && $override->shift) {
            return $override->shift;
        }
        $schedule = $employee->schedules
            ->filter(function ($item) use ($date) {
                return Carbon::parse($item->effective_start_date)->startOfDay()->lte($date)
                    && (!$item->effective_end_date || Carbon::parse($item->effective_end_date)->endOfDay()->gte($date));
            })
            ->sortByDesc(fn ($item) => Carbon::parse($item->effective_start_date)->timestamp)
            ->first();
        if (!$schedule || !$schedule->schedule) {
            return null;
        }
        $length = (int) $schedule->schedule->count_detail;
        if ($length < 1) {
            return null;
        }
        $dayNumber = (Carbon::parse($schedule->effective_start_date)->startOfDay()->diffInDays($date, false) % $length) + 1;
        return optional($schedule->schedule->details->firstWhere('number', $dayNumber))->shift;
    }

    private function approvedTimeoffs($employees, Carbon $start, Carbon $end): array
    {
        $map = [];
        $requests = ApprovalRequest::with(['data', 'type'])
            ->whereIn('requester_employee_id', $employees->pluck('id'))
            ->where('status', 'approved')->get();
        foreach ($requests as $request) {
            $payload = optional($request->data)->payload ?: [];
            $from = data_get($payload, 'start_date') ?? data_get($payload, 'date');
            $to = data_get($payload, 'end_date') ?? $from;
            if (!$from || !$to) continue;
            $current = Carbon::parse($from)->startOfDay()->max($start);
            $last = Carbon::parse($to)->startOfDay()->min($end);
            while ($current->lte($last)) {
                $map[$request->requester_employee_id . '|' . $current->toDateString()] = [
                    'type' => optional($request->type)->name ?? 'Timeoff',
                ];
                $current->addDay();
            }
        }
        return $map;
    }

    private function buildReportCell($attendance, $shift, $leave, Carbon $date): array
    {
        $normalizedShiftName = str_replace([' ', '_', '-'], '', strtolower((string) optional($shift)->name));
        $isDayOff = str_contains($normalizedShiftName, 'dayoff');
        $working = $shift && !$shift->holiday && !$isDayOff;
        $today = Carbon::today($date->getTimezone());
        $storedStatus = strtolower(trim((string) optional($attendance)->status));
        if ($storedStatus === 'absent') {
            $storedStatus = '-';
        }
        $hasAttendanceAction = $attendance && (
            $attendance->check_in ||
            $attendance->check_out ||
            ($storedStatus !== '' && $storedStatus !== '-')
        );
        $lateMinutes = 0;
        if ($hasAttendanceAction && $attendance->check_in && $shift && $shift->schedule_in) {
            $scheduled = Carbon::parse($date->toDateString() . ' ' . $shift->schedule_in);
            $actual = Carbon::parse($attendance->check_in);
            $lateMinutes = max(0, $scheduled->diffInMinutes($actual, false));
        }
        if ($leave) {
            $status = 'TO';
        } elseif ($hasAttendanceAction && ($storedStatus === 'present' || $attendance->check_in || $attendance->check_out)) {
            $status = $lateMinutes > 0 ? 'L' : 'P';
        } elseif ($hasAttendanceAction && $storedStatus !== '-') {
            $status = strtoupper((string) $attendance->status);
        } elseif (!$working) {
            $status = $shift && $shift->holiday && !$isDayOff ? 'H' : 'OFF';
        } elseif ($date->lt($today)) {
            $status = 'A';
        } else {
            $status = '-';
        }
        return [
            'date' => $date->toDateString(),
            'status' => $status,
            'label' => $status === 'P' ? 'Present' : ($status === 'L' ? 'Late' : ($status === 'TO' ? 'Timeoff' : ($status === 'OFF' ? 'Day Off' : ($status === 'H' ? 'Holiday' : ($status === 'A' ? 'Absent' : $status))))),
            'timeoff_type' => $leave['type'] ?? null,
            'late_minutes' => $lateMinutes,
            'attendance_id' => optional($attendance)->id,
        ];
    }

    private function formatMinutes(int $minutes): string
    {
        if ($minutes < 60) return $minutes . 'm';
        return intdiv($minutes, 60) . 'h ' . ($minutes % 60) . 'm';
    }

    public function filterReport(Request $request)
    {
        $_month = $request->input('month');
        $month = Carbon::parse($_month);
        [$startDate, $endDate] = $this->reportPeriod($month->startOfMonth());

        $attendances = Attendance::query()
                        ->whereBetween('date', [$startDate, $endDate])
                        ->get();
        return response()->json($attendances);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
