<?php

namespace App\Services;

interface HolidayService
{
    function get();
    function show($id);
    function post($request);
    function put($holiday, $request);
    function delete($holiday);
    function reconcileDates(array $dates);
}
