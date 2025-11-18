<?php

namespace App\Services;

interface EmployeeService
{
    function get($request);
    function getByJobLevel($request);
    function getByuserId($user_id);
    function paginate($request);
    function show($id);
    function post($request);
    function put($request);
    function delete($id);
}
