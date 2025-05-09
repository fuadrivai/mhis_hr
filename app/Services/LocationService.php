<?php

namespace App\Services;

interface LocationService
{
    function get();
    function show($id);
    function post($request);
    function put($request);
    function delete($id);
}
