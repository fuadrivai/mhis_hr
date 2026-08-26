<?php

namespace App\Services;

interface LeaveAllocationService
{
    function get();
    function getByEmployeeId($employeeId);
    function getActiveByEmployeeId($employeeId);
    function show($id);
    function post($request);
    function put($id, $request);
    function delete($id);
}
