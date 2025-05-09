<?php

namespace App\Services;

interface PersonService
{
    function get($request);
    function show($id);
    function showByemail($email);
    function post($request);
    function put($request);
    function delete($id);
    function getPersonalData($companyId, $request);
}
