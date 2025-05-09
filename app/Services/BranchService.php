<?php

namespace App\Services;

interface BranchService
{
    function get();
    function show($id);
    function post($request);
    function put($id, $request);
    function delete($id);
}
