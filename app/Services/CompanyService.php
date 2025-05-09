<?php

namespace App\Services;

interface CompanyService

{
    function get($request);
    function show($id);
    function post($request);
    function put($id, $request);
    function delete($id);
}
