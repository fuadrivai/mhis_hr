<?php

namespace App\Services;

interface LiveAbsentService
{
    function get($request);
    function getCity($city);
    function filterByUser($request);
    function show($id);
    function post($request);
    function put($id, $request);
    function delete($id);
}
