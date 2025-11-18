<?php

namespace App\Services\Implement;

use App\Models\Employment;
use App\Services\EmploymentService;
use Carbon\Carbon;

class EmploymentImplement implements EmploymentService
{
    function get($request)
    {
    }
    function paginate($request) {}
    function show($id) {}

    function post($request)
    {

    }
    function put($request) {
        try {
            $employment = Employment::findOrFail($request['id']);

            $joinDate = Carbon::parse($request['join_date'])->format('Y-m-d');
            $endDate = null;
            if (!empty($request['end_date'])) {
                $endDate = Carbon::parse($request['end_date'])->format('Y-m-d');
            }

            $employment->update([
                "job_position_id"   => $request['position'],
                "organization_id"   => $request['organization'],
                "job_level_id"      => $request['level'],
                "branch_id"         => $request['branch'],
                "employment_status" => $request['employment-status'],
                "join_date"         => $joinDate,
                "end_date"          => $endDate,
                "approval_line"     => $request['approval_line']
            ]);

            return $employment;
        } catch (\Throwable $th) {
            return $th;
        }
        
    }
    function delete($id) {}

    function getByJobLevel($request)
    {
        
    }
    function getByuserId($userId)
    {
        
    }
}
