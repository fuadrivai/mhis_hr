<?php

namespace App\Services;

interface TimeOffService
{
    function get();
    function show($id);
    function post($request);
    function employeeAssignment($request);
    function put($request);
    function delete($id);
}
