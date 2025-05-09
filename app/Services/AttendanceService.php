<?php

namespace App\Services;

interface AttendanceService
{
    function get($request);
    function getUserScheduleById($request);
    function liveAttendanceList($request);
    function getHistory($request);
    function getSummaryReport($request);
    function show($id);
    function post($request);
    function postAttendance($request);
    function put($request);
    function delete($id);

    function mekariOauth2();
}
