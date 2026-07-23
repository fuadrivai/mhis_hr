<?php

namespace App\Services;

interface RoleService
{
    function get();
    function show($id);
    function post($request);
    function changeUserRole($request);
    function put($request);
    function delete($id);
}
