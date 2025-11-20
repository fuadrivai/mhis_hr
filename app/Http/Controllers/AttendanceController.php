<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\BranchService;
use App\Services\JobLevelService;
use App\Services\OrganizationService;
use App\Services\PositionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        // $attendances = $this->attendanceService->getSummaryReport($request);
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
        $attendances = Attendance::with(['employee.employment','employee.personal']);

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
        return view('attendance.index',
            [
                "title" => "Attendance data",
                "branches"=>$branches,
                "organizations"=> $organizations,
                "positions"=> $positions,
                "levels"=> $levels,
            ]
        );
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
