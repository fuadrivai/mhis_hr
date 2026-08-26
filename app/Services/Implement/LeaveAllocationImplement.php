<?php

namespace App\Services\Implement;

use App\Models\AcademicYear;
use App\Models\LeaveAllocation;
use App\Services\LeaveAllocationService;

class LeaveAllocationImplement implements LeaveAllocationService
{
    function get()
    {
        try {
            $leaves = LeaveAllocation::all();
            return $leaves;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function getByEmployeeId($employeeId)
    {
        try {
            $leaves = LeaveAllocation::where('employee_id', $employeeId)->get();
            return $leaves;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function getActiveByEmployeeId($employeeId)
    {
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        if (!$activeAcademicYear) {
            throw new \Exception("No active academic year found.", 400);
        }
        $leave = LeaveAllocation::where('employee_id', $employeeId)->where('academic_year_id', $activeAcademicYear->id)->first();
        if (!$leave) {
            return null;
        }
        return $leave;
    }
    function show($id) {
        try {
            $level = LeaveAllocation::find($id);
            return $level;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function post($request)
    {
        try {
            $level = new LeaveAllocation();
            $level->name = $request['name'];
            $level->save();
            return $level;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }

    function put($id, $request)
    {
        try {
            LeaveAllocation::where('id', $id)->update([
                "name" => $request["name"],
            ]);
            $level = LeaveAllocation::find($id);
            return $level;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
