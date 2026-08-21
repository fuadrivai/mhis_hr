<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\EmployeeShiftOverride;
use App\Exports\EmployeeScheduleCalendarExport;
use App\Services\EmployeeScheduleService;
use App\Services\EmployeeService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private EmployeeService $employeeService;
    private EmployeeScheduleService $employeeScheduleService;
    private ScheduleService $scheduleService;

    public function __construct(EmployeeService $employeeService, EmployeeScheduleService $employeeScheduleService, ScheduleService $scheduleService)
    {
        $this->employeeService = $employeeService;
        $this->employeeScheduleService = $employeeScheduleService;
        $this->scheduleService = $scheduleService;
    }

    public function index()
    {
        $employee = $this->employeeService->get()->where('is_active', true)->values();
        $schedules = $this->scheduleService->get();
        return view('employee.scheduler.index', [
            "data" => $employee,
            "schedules" => $schedules,
            "branches" => $employee->pluck('employment.branch')->filter()->unique('id')->sortBy('name')->values(),
            "organizations" => $employee->pluck('employment.organization')->filter()->unique('id')->sortBy('name')->values(),
            "levels" => $employee->pluck('employment.job_level')->filter()->unique('id')->sortBy('name')->values(),
            "positions" => $employee->pluck('employment.job_position')->filter()->unique('id')->sortBy('name')->values(),
            "title" => "Scheduler"
        ]);
    }

    public function calendar(Request $request)
    {
        $selectedDate = Carbon::parse($request->query('date', now()->toDateString()))->startOfDay();
        $view = in_array($request->query('view'), ['weekly', 'biweekly', 'monthly'], true)
            ? $request->query('view')
            : 'weekly';
        $search = trim((string) $request->query('search', ''));
        $filters = [
            'branch' => strtolower(trim((string) $request->query('branch', ''))),
            'organization' => strtolower(trim((string) $request->query('organization', ''))),
            'level' => strtolower(trim((string) $request->query('level', ''))),
            'position' => strtolower(trim((string) $request->query('position', ''))),
        ];

        $dates = $this->buildDateRange($selectedDate, $view);

        $employees = Employee::with([
            'personal',
            'employment.branch',
            'employment.organization',
            'schedules.schedule.details.shift',
            'shiftOverrides.shift'
        ])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($search !== '' || array_sum(array_map('strlen', $filters)) > 0) {
            $searchValue = strtolower($search);
            $employees = $employees->filter(function ($employee) use ($searchValue, $filters) {
                $name = strtolower((string) ($employee->personal ? $employee->personal->fullname : ''));
                $employeeCode = strtolower((string) ($employee->employment ? $employee->employment->employee_id : ''));
                $employment = $employee->employment;
                $branch = strtolower((string) optional(optional($employment)->branch)->name);
                $organization = strtolower((string) optional(optional($employment)->organization)->name);
                $level = strtolower((string) optional(optional($employment)->job_level)->name);
                $position = strtolower((string) optional(optional($employment)->job_position)->name);

                return ($searchValue === '' || str_contains($name, $searchValue) || str_contains($employeeCode, $searchValue))
                    && ($filters['branch'] === '' || $branch === $filters['branch'])
                    && ($filters['organization'] === '' || $organization === $filters['organization'])
                    && ($filters['level'] === '' || $level === $filters['level'])
                    && ($filters['position'] === '' || $position === $filters['position']);
            })->values();
        }

        return response()->json([
            'view' => $view,
            'search' => $search,
            'selected_date' => $selectedDate->toDateString(),
            'range_label' => $this->buildRangeLabel($dates, $view),
            'dates' => $dates->map(function ($date) {
                return [
                    'date' => $date->toDateString(),
                    'label' => $date->format('D, d M'),
                    'day_name' => $date->format('l'),
                    'day_number' => $date->format('d'),
                    'is_today' => $date->isToday(),
                ];
            })->values(),
            'employees' => $employees->map(function ($employee) use ($dates) {
                $employeeCells = [];

                foreach ($dates as $date) {
                    $employeeCells[] = $this->resolveEmployeeCell($employee, $date);
                }

                return [
                    'id' => $employee->id,
                    'name' => $employee->personal ? $employee->personal->fullname : 'Unknown Employee',
                    'employee_code' => $employee->employment ? $employee->employment->employee_id : null,
                    'branch' => optional(optional($employee->employment)->branch)->name,
                    'organization' => optional(optional($employee->employment)->organization)->name,
                    'level' => optional(optional($employee->employment)->job_level)->name,
                    'position' => optional(optional($employee->employment)->job_position)->name,
                    'avatar' => optional($employee->personal)->avatar
                        ? asset('storage/' . $employee->personal->avatar)
                        : asset('images/user.png'),
                    'cells' => $employeeCells,
                ];
            })->values(),
        ]);
    }

    public function exportCalendar(Request $request)
    {
        $selectedDate = Carbon::parse($request->query('date', now()->toDateString()))->startOfDay();
        $view = in_array($request->query('view'), ['weekly', 'biweekly', 'monthly'], true)
            ? $request->query('view')
            : 'weekly';
        $search = trim((string) $request->query('search', ''));
        $filters = [
            'branch' => strtolower(trim((string) $request->query('branch', ''))),
            'organization' => strtolower(trim((string) $request->query('organization', ''))),
            'level' => strtolower(trim((string) $request->query('level', ''))),
            'position' => strtolower(trim((string) $request->query('position', ''))),
        ];
        $dates = $this->buildDateRange($selectedDate, $view);

        $employees = Employee::with(['personal', 'employment', 'schedules.schedule.details.shift', 'shiftOverrides.shift'])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($search !== '' || array_sum(array_map('strlen', $filters)) > 0) {
            $searchValue = strtolower($search);
            $employees = $employees->filter(function ($employee) use ($searchValue, $filters) {
                $name = strtolower((string) ($employee->personal ? $employee->personal->fullname : ''));
                $employeeCode = strtolower((string) ($employee->employment ? $employee->employment->employee_id : ''));
                $employment = $employee->employment;
                $branch = strtolower((string) optional(optional($employment)->branch)->name);
                $organization = strtolower((string) optional(optional($employment)->organization)->name);
                $level = strtolower((string) optional(optional($employment)->job_level)->name);
                $position = strtolower((string) optional(optional($employment)->job_position)->name);

                return ($searchValue === '' || str_contains($name, $searchValue) || str_contains($employeeCode, $searchValue))
                    && ($filters['branch'] === '' || $branch === $filters['branch'])
                    && ($filters['organization'] === '' || $organization === $filters['organization'])
                    && ($filters['level'] === '' || $level === $filters['level'])
                    && ($filters['position'] === '' || $position === $filters['position']);
            })->values();
        }

        $headings = ['No', 'Employee Name', 'Employee ID'];
        foreach ($dates as $date) {
            $headings[] = $date->format('D d M Y');
        }

        $rows = $employees->map(function ($employee) use ($dates) {
            $days = [];
            foreach ($dates as $date) {
                $cell = $this->resolveEmployeeCell($employee, $date);
                $days[] = $cell['type'] === 'schedule'
                    ? trim($cell['shift_name'] . ' | ' . $cell['time_text'])
                    : ucfirst($cell['label']);
            }

            return [
                'employee_name' => $employee->personal ? $employee->personal->fullname : 'Unknown Employee',
                'employee_code' => $employee->employment ? $employee->employment->employee_id : null,
                'days' => $days,
            ];
        });

        return Excel::download(
            new EmployeeScheduleCalendarExport($rows, $headings),
            'employee-schedule-' . $view . '-' . $selectedDate->format('Ymd') . '.xlsx'
        );
    }

    private function buildDateRange(Carbon $selectedDate, string $view)
    {
        $dates = collect();

        if ($view === 'monthly') {
            $start = $selectedDate->copy()->startOfMonth();
            $end = $selectedDate->copy()->endOfMonth();
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $dates->push($date->copy());
            }
            return $dates;
        }

        $start = $this->getPeriodStart($selectedDate, $view);
        $length = $view === 'biweekly' ? 14 : 7;

        for ($i = 0; $i < $length; $i++) {
            $dates->push($start->copy()->addDays($i));
        }

        return $dates;
    }

    private function getPeriodStart(Carbon $date, string $view): Carbon
    {
        if ($view === 'monthly') {
            return $date->copy()->startOfMonth();
        }

        $base = Carbon::parse('2024-01-01')->startOfWeek(Carbon::MONDAY);
        $periodStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $diffWeeks = (int) floor($base->diffInDays($periodStart, false) / 7);

        if ($view === 'biweekly') {
            $multiplier = (int) floor($diffWeeks / 2) * 2;
            return $base->copy()->addWeeks($multiplier);
        }

        return $periodStart;
    }

    private function buildRangeLabel($dates, string $view): string
    {
        if ($dates->isEmpty()) {
            return '';
        }

        $start = $dates->first();
        $end = $dates->last();

        if ($view === 'monthly') {
            return $start->translatedFormat('F Y');
        }

        if ($view === 'biweekly') {
            return $start->format('d') . ' - ' . $end->format('d M Y');
        }

        return $start->format('d') . ' - ' . $end->format('d M Y');
    }

    private function resolveEmployeeCell($employee, Carbon $date)
    {
        $override = $employee->shiftOverrides
            ->first(function ($entry) use ($date) {
                return $entry->date && $entry->date->toDateString() === $date->toDateString();
            });

        if ($override && $override->shift) {
            $shift = $override->shift;
            $shiftName = $shift->name ?: 'Override';

            return [
                'date' => $date->toDateString(),
                'type' => 'override',
                'label' => $shiftName,
                'time_text' => $this->formatShiftRange($shift),
                'employee_schedule_id' => null,
                'schedule_id' => null,
                'schedule_detail_id' => null,
                'shift_id' => $shift->id,
                'override_id' => $override->id,
                'schedule_name' => 'Shift Override',
                'shift_name' => $shiftName,
                'is_today' => $date->isToday(),
            ];
        }

        $schedule = $employee->schedules
            ->filter(function ($entry) use ($date) {
                if (empty($entry->effective_start_date)) {
                    return false;
                }

                $start = Carbon::parse($entry->effective_start_date)->startOfDay();
                if ($date->lt($start)) {
                    return false;
                }

                if (!empty($entry->effective_end_date)) {
                    $end = Carbon::parse($entry->effective_end_date)->endOfDay();
                    if ($date->gt($end)) {
                        return false;
                    }
                }

                return true;
            })
            ->sortByDesc(function ($entry) {
                return Carbon::parse($entry->effective_start_date)->timestamp;
            })
            ->first();

        $detail = null;
        $shift = null;

        if ($schedule && $schedule->schedule && $schedule->schedule->details) {
            $detail = $schedule->schedule->details
                ->first(function ($item) use ($date) {
                    $storedDay = strtolower(trim((string) $item->day));
                    $dateDayName = strtolower($date->format('l'));
                    $dateDayShortName = strtolower($date->format('D'));

                    if ($storedDay === $dateDayName || $storedDay === $dateDayShortName) {
                        return true;
                    }

                    if (preg_match('/^day\s*(\d+)$/', $storedDay, $matches)) {
                        return (int) $matches[1] === (int) $date->format('N');
                    }

                    return false;
                });

            if ($detail) {
                $shift = $detail->shift;
            }
        }

        $isHoliday = ($shift && (bool) $shift->holiday) || ($detail && !empty($detail->shift_name) && str_contains(strtolower($detail->shift_name), 'holiday'));
        $isDayOff = !$detail || !$shift || str_contains(strtolower((string) ($detail->shift_name ?? $shift->name ?? '')), 'dayoff');

        $type = $isHoliday ? 'holiday' : ($isDayOff ? 'dayoff' : 'schedule');

        $timeText = '00:00 - 00:00';
        $label = 'dayoff';
        $shiftName = null;

        if ($shift) {
            $shiftName = $detail && $detail->shift_name ? $detail->shift_name : ($shift->name ?? null);
            if ($type === 'schedule') {
                $label = $shiftName ?: 'Schedule';
                $timeText = $this->formatShiftRange($shift);
            } elseif ($isHoliday) {
                $label = $shiftName ?: 'Holiday';
            }
        }

        if ($type === 'dayoff') {
            $label = 'dayoff';
            $timeText = '00:00 - 00:00';
        }

        return [
            'date' => $date->toDateString(),
            'type' => $type,
            'label' => $label,
            'time_text' => $timeText,
            'employee_schedule_id' => $schedule ? $schedule->id : null,
            'schedule_id' => $schedule ? $schedule->schedule_id : null,
            'schedule_detail_id' => $detail ? $detail->id : null,
            'shift_id' => $shift ? $shift->id : null,
            'override_id' => null,
            'schedule_name' => $schedule ? ($schedule->schedule_name ?? null) : null,
            'shift_name' => $shiftName,
            'is_today' => $date->isToday(),
        ];
    }

    private function formatShiftRange($shift): string
    {
        if (!$shift) {
            return '00:00 - 00:00';
        }

        $in = $shift->schedule_in ?? '00:00';
        $out = $shift->schedule_out ?? '00:00';

        return trim($in) . ' - ' . trim($out);
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
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'schedule_id' => 'required|exists:schedules,id',
                'schedule_name' => 'required|string|max:255',
                'effective_start_date' => 'required|date',
            ]);

            $data = $this->employeeScheduleService->post($validated);

            return response()->json($data);
        } catch (\Throwable $th) {
            $status = ($th->getCode() && $th->getCode() >= 100 && $th->getCode() < 600) ? $th->getCode() : 500;
            return response()->json([
                'status' => 'Failed to assign schedule',
                'message' => $th->getMessage()
            ], $status);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmployeeSchedule  $employeeSchedule
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeSchedule $employeeSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmployeeSchedule  $employeeSchedule
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeSchedule $employeeSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmployeeSchedule  $employeeSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeSchedule $employeeSchedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmployeeSchedule  $employeeSchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeSchedule $employeeSchedule)
    {
        //
    }
}
