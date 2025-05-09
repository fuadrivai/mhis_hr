<?php

namespace App\Services;

interface PinLocationService
{
    function get($request);
    function show($id);
    function post($request);
    function put($id, $request);
    function delete($id);
}
