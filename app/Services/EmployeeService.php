<?php

namespace App\Services;

interface EmployeeService
{
    function get($request);
    function getByJobLevel($request);
    function getByuserId($user_id);
    function paginate($request);
    function show($id,$with=[]);
    function post($request, $driveService);
    function put($request);
    function delete($id);
    function deleteDocument($document_id, $driveService);
    function documentUpload($employeeId, $request, $driveService);
}
