<?php

namespace App\Services;

interface ApprovalRuleService
{
    function get($with = []);
    function show($id);
    function post($request);
    function put($request);
    function delete($id);
}
