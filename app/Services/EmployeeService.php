<?php

namespace App\Services;

interface EmployeeService
{
    function get();
    function getActive();
    function getByJobLevel($request);
    function getByuserId($userId);
    function paginate($request);
    function show($id,$with=[]);
    function post($request, $driveService);
    function put($request);
    function delete($id);
    function deactivate($employeeIds);
    function deleteDocument($document_id, $driveService);
    function documentUpload($employeeId, $request, $driveService);
    function getProfile($user);
}
