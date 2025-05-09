<?php

namespace App\Services;

interface PositionService
{
    function get();
    function show($id);
    function post($request);
    function put($id, $request);
    function delete($id);
}
