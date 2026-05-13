<?php

namespace App\Services;

interface EmployeeService
{
    function get();
    function getActive();
    function getByJobLevel($request);
    function getByuserId($user_id);
    function paginate($request);
    function show($id,$with=[]);
    function post($request, $driveService);
    function put($request);
    function delete($id);
    function deactivate($employeeIds);
    function deleteDocument($document_id, $driveService);
    function documentUpload($employeeId, $request, $driveService);
}
