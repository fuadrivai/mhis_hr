<?php

namespace App\Http\Controllers;

use App\Models\LeaveAllocation;
use App\Models\LeaveAllocationHistory;
use App\Models\Branch;
use App\Models\JobLevel;
use App\Models\Organization;
use App\Models\Position;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;

class LeaveAllocationController extends Controller
{

private AcademicYearService $academicYearService ;


public function __construct(AcademicYearService $academicYearService){
    
    $this->academicYearService = $academicYearService;
}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $activeAY = $this->academicYearService->getActiveAcademicYear();
        $perPage = in_array((int) $request->input('per_page', 10), [5, 10, 20, 50, 100], true)
            ? (int) $request->input('per_page', 10)
            : 10;
        $order = $request->input('order', 'remaining_asc');

        $leaveAllocations = LeaveAllocation::with([
            'employee.personal',
            'employee.employment',
            'timeoff',
            'academicYear',
        ])
            ->where('academic_year_id', $activeAY->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->whereHas('employee.personal', function ($personalQuery) use ($search) {
                    $personalQuery->where('fullname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('branch') && $request->input('branch') !== 'all', function ($query) use ($request) {
                $query->whereHas('employee.employment', function ($employmentQuery) use ($request) {
                    $employmentQuery->where('branch_id', $request->input('branch'));
                });
            })
            ->when($request->filled('organization') && $request->input('organization') !== 'all', function ($query) use ($request) {
                $query->whereHas('employee.employment', function ($employmentQuery) use ($request) {
                    $employmentQuery->where('organization_id', $request->input('organization'));
                });
            })
            ->when($request->filled('level') && $request->input('level') !== 'all', function ($query) use ($request) {
                $query->whereHas('employee.employment', function ($employmentQuery) use ($request) {
                    $employmentQuery->where('job_level_id', $request->input('level'));
                });
            })
            ->when($request->filled('position') && $request->input('position') !== 'all', function ($query) use ($request) {
                $query->whereHas('employee.employment', function ($employmentQuery) use ($request) {
                    $employmentQuery->where('job_position_id', $request->input('position'));
                });
            })
            ->orderBy('remaining', $order === 'remaining_desc' ? 'desc' : 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return view('settings.leave-allocation.index',[
            "title"=>"Leave Allocation",
            "leaveAllocations"=>$leaveAllocations,
            "activeAY"=>$activeAY,
            'branches' => Branch::orderBy('name')->get(),
            'organizations' => Organization::orderBy('name')->get(),
            'levels' => JobLevel::orderBy('name')->get(),
            'positions' => Position::orderBy('name')->get(),
        ] );
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
     * @param  \App\Models\LeaveAllocation  $leaveAllocation
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveAllocation $leaveAllocation)
    {
        return response()->json(
            $leaveAllocation->load([
                'histories' => function ($query) {
                    $query->latest();
                },
            ])
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LeaveAllocation  $leaveAllocation
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveAllocation $leaveAllocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeaveAllocation  $leaveAllocation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveAllocation $leaveAllocation)
    {
        $validated = $request->validate([
            'remaining' => ['required', 'integer', 'min:0'],
        ]);

        $previousRemaining = $leaveAllocation->remaining;
        $remaining = $validated['remaining'];

        if ($previousRemaining !== $remaining) {
            $leaveAllocation->update(['remaining' => $remaining]);

            LeaveAllocationHistory::create([
                'leave_allocation_id' => $leaveAllocation->id,
                'type' => 'adjustment',
                'days' => $remaining - $previousRemaining,
                'remark' => 'Remaining balance adjusted manually.',
            ]);
        }

        return response()->json($leaveAllocation->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LeaveAllocation  $leaveAllocation
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveAllocation $leaveAllocation)
    {
        //
    }
}
