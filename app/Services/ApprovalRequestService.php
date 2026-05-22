<?php

namespace App\Services;

interface ApprovalRequestService
{
    function get($with = []);
    function getRequestByUser($request);
    function getApprovalByUser($request);
    function show($id);
    function post($request);
    function put($request);
    function delete($id);
    function action($data);
    function cancel($data);
}
