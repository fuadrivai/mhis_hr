<?php

namespace App\Services\Implement;

use App\Models\AcademicYear;
use App\Services\AcademicYearService;

class AcademicYearImplement implements AcademicYearService
{
    function get()
    {
        try {
            $academicYears = AcademicYear::all();
            return $academicYears;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id) {
        try {
            $academicYear = AcademicYear::find($id);
            if (!$academicYear) {
                return response()->json(["message" => "Academic year not found"], 404);
            }
            return $academicYear;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function post($request)
    {
        try {
            $academicYear = new AcademicYear();
            $academicYear->name = $request['name'];
            $academicYear->start_date = $request['start_date'];
            $academicYear->end_date = $request['end_date'];
            $academicYear->is_active = $request['is_active'] ==true ? 1 : 0;
            $academicYear->save();
            return $academicYear;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($request) {
        try {
            $academicYearId = $request['id'] ?? null;
            $academicYear = AcademicYear::find($academicYearId);
            if (!$academicYear) {
                return response()->json(["message" => "Academic year not found"], 404);
            }
            $academicYear->name = $request['name'];
            $academicYear->start_date = $request['start_date'];
            $academicYear->end_date = $request['end_date'];
            $academicYear->is_active = isset($request['is_active']) ? 1 : 0;
            $academicYear->save();
            return $academicYear;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {
        try {
            $academicYear = AcademicYear::find($id);
            if (!$academicYear) {
                return response()->json(["message" => "Academic year not found"], 404);
            }
            $academicYear->delete();
            return response()->json(["message" => "Academic year deleted successfully"]);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
}
