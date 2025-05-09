<?php

namespace App\Services;

interface PayslipService
{
    function get($request);
    function show($id);
    function post($request);
    function put($request);
    function delete($id);
}
