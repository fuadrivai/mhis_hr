<?php

namespace App\Services;

interface AttendanceLogService
{
    function get($request);
    function show($id);
    function showByEmployeeId($employeeId);
    function clock_in($request);
    function clock_out($request);
    function put($request);
    function delete($id);
}
