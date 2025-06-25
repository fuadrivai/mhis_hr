<?php

namespace App\Services\Implement;

use App\Services\PersonalService;

class PersonalImplement implements PersonalService
{
    function get($request) {}
    function show($id) {}
    function post($request) {}
    function put($request)
    {
        try {
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
