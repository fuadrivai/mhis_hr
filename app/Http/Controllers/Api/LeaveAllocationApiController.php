<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveAllocation;
use App\Services\LeaveAllocationService;
use Illuminate\Http\Request;

class LeaveAllocationApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private LeaveAllocationService $leaveAllocationService;
    function __construct(LeaveAllocationService $leaveAllocationService)
    {
        $this->leaveAllocationService = $leaveAllocationService;
    }
    public function index()
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

    public function getActiveByEmployeeId($employeeId)
    {
        try {
            $leave = $this->leaveAllocationService->getActiveByEmployeeId($employeeId);
            return response()->json($leave);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
}
