<?php

namespace App\Services;

interface AcademicYearService
{
    function get();
    function getActiveAcademicYear();
    function show($id);
    function post($request);
    function put($request);
    function delete($id);
}
