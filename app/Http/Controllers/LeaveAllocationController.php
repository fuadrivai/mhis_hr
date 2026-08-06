<?php

namespace App\Http\Controllers;

use App\Models\LeaveAllocation;
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
    public function index()
    {
        $activeAY = $this->academicYearService->getActiveAcademicYear();
        $leaveAllocations = LeaveAllocation::with(['employee.personal','employee.employment', 'timeoff', 'academicYear'])->where('academic_year_id', $activeAY->id)->get();

        return view('settings.leave-allocation.index',[
            "title"=>"Leave Allocation",
            "leaveAllocations"=>$leaveAllocations,
            "activeAY"=>$activeAY
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
        //
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
        //
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
