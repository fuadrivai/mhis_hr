<?php

namespace App\Services;

interface ApprovalRequestService
{
    function get($with = []);
    function show($id);
    function post($request);
    function put($request);
    function delete($id);
}
