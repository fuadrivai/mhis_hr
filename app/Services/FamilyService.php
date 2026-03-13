<?php

namespace App\Services;

interface FamilyService
{
    function get();
    function show($id);
    function post($request);
    function put($request);
    function delete($id);
}
