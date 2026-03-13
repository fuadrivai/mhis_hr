<?php

namespace App\Services;

interface EmergencyContactService

{
    function get();
    function show($id);
    function post($request);
    function put($id, $request);
    function delete($id);
}
