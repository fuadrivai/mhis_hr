<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Shift;
use App\Services\BranchService;
use App\Services\JobLevelService;
use App\Services\OrganizationService;
use App\Services\PositionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Utilities\Request as UtilitiesRequest;
use Illuminate\Support\Facades\File;


class AttendanceController extends Controller
{

    private BranchService $branchService;
    private OrganizationService $organizationService;
    private PositionService $positionService;
    private JobLevelService $jobLevelService;
    
    public function __construct(
        BranchService $branchService,
        OrganizationService $organizationService,
        PositionService $positionService,
        JobLevelService $jobLevelService
        )
    {
        $this->branchService = $branchService;
        $this->organizationService = $organizationService;
        $this->positionService = $positionService;
        $this->jobLevelService = $jobLevelService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $date = Carbon::now();
        $now = $date->format('F d, Y');
        return view('layouts.late-layout', [
            "title" => "Master Employee",
            "date" => $now
        ]);
    }
    public function liveAttendance()
    {
        $path = public_path('live-attendance/index.html');

        if (!File::exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
    public function attendance(UtilitiesRequest $request)
    {
        
        $attendances = Attendance::with(['employee.employment','employee.personal','logs']);
        
        $user = auth()->user();
        if ($user && $user->roles->contains('id', 3)) {
            if ($user->employee && $user->employee->employment) {
                $branchId = $user->employee->employment->branch_id;
                $orgId = $user->employee->employment->organization_id;
                $attendances->whereHas('employee.employment', function ($q) use ($branchId, $orgId) {
                    $q->where('branch_id', $branchId)
                      ->where('organization_id', $orgId);
                });
            } else {
                $attendances->where('id', 0);
            }
        }

        if ($request->date && $request->date != '') {
            $_date = Carbon::parse($request->date)->format('Y-m-d');
            $attendances->where('date',$_date);
        }

        if ($request->branch && $request->branch != '') {
            if($request->branch != 'all'){
                $attendances->whereHas('employee.employment', function ($query) use ($request) {
                    $query->where('branch_id', $request->branch);
                });
            }

        }

        if ($request->organization && $request->organization != '') {
            if($request->organization != 'all'){
                $attendances->whereHas('employee.employment', function ($query) use ($request) {
                    $query->where('organization_id', $request->organization);
                });
            }
        }

        if ($request->position && $request->position != '') {
            if($request->position != 'all'){
                $attendances->whereHas('employee.employment', function ($query) use ($request) {
                    $query->where('job_position_id', $request->position);
                });
            }
        }

        if ($request->level && $request->level != '') {
            if($request->level != 'all'){
                $attendances->whereHas('employee.employment', function ($query) use ($request) {
                    $query->where('job_level_id', $request->level);
                });
            }
        }

        if ($request->ajax()) {
            return datatables()->of($attendances)->make(true);
        }

        $branches = $this->branchService->get();
        $organizations = $this->organizationService->get();
        $positions = $this->positionService->get();
        $levels = $this->jobLevelService->get();
        $shifts = Shift::orderBy('name')->get();
        return view('attendance.index',
            [
                "title" => "Attendance data",
                "branches"=>$branches,
                "organizations"=> $organizations,
                "positions"=> $positions,
                "levels"=> $levels,
                "shifts" => $shifts,
            ]
        );
    }

    public function attendanceSummary(Request $request)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))->format('Y-m-d')
            : Carbon::today()->format('Y-m-d');

        $employees = Employee::query()->where('is_active', 1);
        $attendances = Attendance::query()->where('date', $date);

        $user = auth()->user();
        if ($user && $user->roles->contains('id', 3)) {
            if ($user->employee && $user->employee->employment) {
                $branchId = $user->employee->employment->branch_id;
                $orgId = $user->employee->employment->organization_id;
                $attendances->whereHas('employee.employment', function ($q) use ($branchId, $orgId) {
                    $q->where('branch_id', $branchId)
                    ->where('organization_id', $orgId);
                });
                $employees->whereHas('employment', function ($q) use ($branchId, $orgId) {
                    $q->where('branch_id', $branchId)
                    ->where('organization_id', $orgId);
                });
            } else {
                $employees->where('id', 0);
                $attendances->where('id', 0);
            }
        }

        foreach ([
            'branch' => 'branch_id',
            'organization' => 'organization_id',
            'position' => 'job_position_id',
            'level' => 'job_level_id',
        ] as $filter => $column) {
            if ($request->filled($filter) && $request->input($filter) !== 'all') {
                $employees->whereHas('employment', function ($query) use ($column, $request, $filter) {
                    $query->where($column, $request->input($filter));
                });
                $attendances->whereHas('employee.employment', function ($query) use ($column, $request, $filter) {
                    $query->where($column, $request->input($filter));
                });
            }
        }

        $present = (clone $attendances)->where('status', 'present')->count();

        return response()->json([
            'present' => $present,
            'absent' => max(0, $employees->count() - $present),
            'late' => (clone $attendances)
                ->where('status', 'present')
                ->whereNotNull('check_in')
                ->whereNotNull('schedule_in')
                 ->whereRaw(
                    'TIME(check_in) >= ADDTIME(CAST(schedule_in AS TIME), "00:01:00")'
                )
                ->count(),
        ]);
    }

    public function attendanceSummaryList(Request $request, string $type)
    {
        if (!in_array($type, ['present', 'absent', 'late'], true)) {
            abort(404);
        }

        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))->format('Y-m-d')
            : Carbon::today()->format('Y-m-d');

        $filters = [
            'branch' => 'branch_id',
            'organization' => 'organization_id',
            'position' => 'job_position_id',
            'level' => 'job_level_id',
        ];

        $user = auth()->user();

        if ($type === 'absent') {
            $employees = Employee::with(['personal', 'employment.branch', 'employment.organization', 'employment.job_position'])
                ->where('is_active', 1)
                ->whereDoesntHave('attendances', function ($query) use ($date) {
                    $query->where('date', $date)->where('status', 'present');
                });
            
                if ($user && $user->roles->contains('id', 3)) {
                    if ($user->employee && $user->employee->employment) {
                        $branchId = $user->employee->employment->branch_id;
                        $orgId = $user->employee->employment->organization_id;
                        $employees->whereHas('employment', function ($q) use ($branchId, $orgId) {
                            $q->where('branch_id', $branchId)
                            ->where('organization_id', $orgId);
                        });
                    } else {
                        $employees->where('id', 0);
                    }
                }

            foreach ($filters as $filter => $column) {
                if ($request->filled($filter) && $request->input($filter) !== 'all') {
                    $employees->whereHas('employment', function ($query) use ($column, $request, $filter) {
                        $query->where($column, $request->input($filter));
                    });
                }
            }

            return response()->json($employees->get()->map(function ($employee) {
                return [
                    'name' => $employee->personal->fullname ?? '-',
                    'branch' => $employee->employment->branch->name ?? '-',
                    'organization' => $employee->employment->organization->name ?? '-',
                    'position' => $employee->employment->job_position->name ?? '-',
                    'schedule_in' => '-',
                    'check_in' => '-',
                ];
            }));
        }

        $attendances = Attendance::with(['employee.personal', 'employee.employment.branch', 'employee.employment.organization', 'employee.employment.job_position'])
            ->where('date', $date)
            ->where('status', 'present');
        
            if ($user && $user->roles->contains('id', 3)) {
                if ($user->employee && $user->employee->employment) {
                    $branchId = $user->employee->employment->branch_id;
                    $orgId = $user->employee->employment->organization_id;
                    $attendances->whereHas('employee.employment', function ($q) use ($branchId, $orgId) {
                        $q->where('branch_id', $branchId)
                        ->where('organization_id', $orgId);
                    });
                } else {
                    $attendances->where('id', 0);
                }
            }

        if ($type === 'late') {
            $attendances->where('status', 'present')
                ->whereNotNull('check_in')
                ->whereNotNull('schedule_in')
                ->whereRaw(
                    'TIME(check_in) >= ADDTIME(CAST(schedule_in AS TIME), "00:01:00")'
                );
        }

        foreach ($filters as $filter => $column) {
            if ($request->filled($filter) && $request->input($filter) !== 'all') {
                $attendances->whereHas('employee.employment', function ($query) use ($column, $request, $filter) {
                    $query->where($column, $request->input($filter));
                });
            }
        }

        return response()->json($attendances->get()->map(function ($attendance) {
            return [
                'name' => $attendance->employee->personal->fullname ?? $attendance->fullname ?? '-',
                'branch' => $attendance->employee->employment->branch->name ?? '-',
                'organization' => $attendance->employee->employment->organization->name ?? '-',
                'position' => $attendance->employee->employment->job_position->name ?? '-',
                'schedule_in' => $attendance->schedule_in ?? '-',
                'check_in' => optional($attendance->check_in)->format('H:i') ?? '-',
            ];
        }));
    }

    public function attendanceLogs(UtilitiesRequest $request, Attendance $attendance)
    {
        $logs = AttendanceLog::where('attendance_id', $attendance->id)
            ->latest('clock_datetime');

        return datatables()->of($logs)->make(true);
    }

    public function datatable(){
        
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
        $validated = $request->validate([
            'shift_id' => ['required', 'exists:shifts,id'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
        ]);

        $attendance = Attendance::findOrFail($id);
        abort_if($attendance->is_locked, 423, 'Attendance is locked.');
        $shift = Shift::findOrFail($validated['shift_id']);
        $oldClockIn = optional($attendance->check_in)->format('H:i') ?: '--';
        $oldClockOut = optional($attendance->check_out)->format('H:i') ?: '--';
        $newClockIn = $validated['check_in'] ?? '--';
        $newClockOut = $validated['check_out'] ?? '--';
        $description = sprintf(
            'Shift: %s to %s; Clock in: %s to %s; Clock out: %s to %s',
            $attendance->shift_name ?: '--',
            $shift->name,
            $oldClockIn,
            $newClockIn,
            $oldClockOut,
            $newClockOut
        );

        $toDateTime = function (?string $time) use ($attendance) {
            return $time ? Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $time) : null;
        };

        DB::transaction(function () use ($attendance, $shift, $validated, $toDateTime, $description) {
            $attendance->update([
                'shift_name' => $shift->name,
                'schedule_in' => $shift->schedule_in,
                'schedule_out' => $shift->schedule_out,
                'check_in' => $toDateTime($validated['check_in'] ?? null),
                'check_out' => $toDateTime($validated['check_out'] ?? null),
            ]);

            AttendanceLog::create([
                'employee_id' => $attendance->employee_id,
                'attendance_id' => $attendance->id,
                'type' => 'edit',
                'clock_datetime' => now(),
                'clock_date' => $attendance->date,
                'time' => now()->format('H:i:s'),
                'has_location' => false,
                'fullname' => $attendance->fullname,
                'shift_name' => $attendance->shift_name,
                'description' => $description,
            ]);
        });

        return response()->json(['message' => 'Attendance updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $isAdmin = $user && $user->roles->contains(function ($role) {
            return strtolower($role->name) === 'admin';
        });
        abort_unless($isAdmin, 403);

        $attendance = Attendance::findOrFail($id);
        abort_if($attendance->is_locked, 423, 'Attendance is locked.');
        $now = now();
        $description = sprintf(
            'Attendance cleared. Clock in: %s; Clock out: %s; Check-in photo: %s; Check-out photo: %s; '
                . 'Check-in location: %s, %s (radius %s); Check-out location: %s, %s (radius %s)',
            optional($attendance->check_in)->format('H:i') ?: '--',
            optional($attendance->check_out)->format('H:i') ?: '--',
            $attendance->check_in_photo ?: 'none',
            $attendance->check_out_photo ?: 'none',
            $attendance->check_in_latitude ?? '--',
            $attendance->check_in_longitude ?? '--',
            $attendance->check_in_radius ?? '--',
            $attendance->check_out_latitude ?? '--',
            $attendance->check_out_longitude ?? '--',
            $attendance->check_out_radius ?? '--'
        );

        DB::transaction(function () use ($attendance, $now, $description) {
            $attendance->update([
                'check_in' => null,
                'check_in_photo' => null,
                'check_in_latitude' => null,
                'check_in_longitude' => null,
                'check_in_radius' => null,
                'check_out' => null,
                'check_out_photo' => null,
                'check_out_latitude' => null,
                'check_out_longitude' => null,
                'check_out_radius' => null,
            ]);

            AttendanceLog::create([
                'employee_id' => $attendance->employee_id,
                'attendance_id' => $attendance->id,
                'type' => 'delete',
                'clock_datetime' => $now,
                'clock_date' => $attendance->date,
                'time' => $now->format('H:i:s'),
                'has_location' => false,
                'fullname' => $attendance->fullname,
                'shift_name' => $attendance->shift_name,
                'description' => $description,
            ]);
        });

        return response()->json(['message' => 'Attendance cleared successfully.']);
    }

    public function lock($id)
    {
        $user = auth()->user();
        $isAdmin = $user && $user->roles->contains(function ($role) {
            return strtolower($role->name) === 'admin';
        });
        abort_unless($isAdmin, 403);

        $attendance = Attendance::findOrFail($id);
        abort_if($attendance->is_locked, 423, 'Attendance is already locked.');
        abort_if(!$attendance->check_in && !$attendance->check_out, 422, 'Cannot lock cleared attendance.');
        $now = now();

        DB::transaction(function () use ($attendance, $now) {
            $attendance->update(['is_locked' => true]);

            AttendanceLog::create([
                'employee_id' => $attendance->employee_id,
                'attendance_id' => $attendance->id,
                'type' => 'lock',
                'clock_datetime' => $now,
                'clock_date' => $attendance->date,
                'time' => $now->format('H:i:s'),
                'has_location' => false,
                'fullname' => $attendance->fullname,
                'shift_name' => $attendance->shift_name,
                'description' => 'Attendance locked. Editing and clearing are disabled.',
            ]);
        });

        return response()->json(['message' => 'Attendance locked successfully.']);
    }

    public function unlock($id)
    {
        $user = auth()->user();
        $isAdmin = $user && $user->roles->contains(function ($role) {
            return strtolower($role->name) === 'admin';
        });
        abort_unless($isAdmin, 403);

        $attendance = Attendance::findOrFail($id);
        abort_if(!$attendance->is_locked, 422, 'Attendance is not locked.');
        $now = now();

        DB::transaction(function () use ($attendance, $now) {
            $attendance->update(['is_locked' => false]);

            AttendanceLog::create([
                'employee_id' => $attendance->employee_id,
                'attendance_id' => $attendance->id,
                'type' => 'unlock',
                'clock_datetime' => $now,
                'clock_date' => $attendance->date,
                'time' => $now->format('H:i:s'),
                'has_location' => false,
                'fullname' => $attendance->fullname,
                'shift_name' => $attendance->shift_name,
                'description' => 'Attendance unlocked. Editing and clearing are enabled.',
            ]);
        });

        return response()->json(['message' => 'Attendance unlocked successfully.']);
    }
}
