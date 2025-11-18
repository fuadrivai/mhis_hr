<?php

namespace App\Services\Implement;

use App\Models\Organization;
use App\Services\OrganizationService;

class OrganizationImplement implements OrganizationService
{
    function get()
    {
        try {
            return Organization::all();
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id) {}
    function post($request)
    {
        try {
            $organization = new Organization();
            $organization->name = $request['name'];
            $organization->save();
            return $organization;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($id, $request)
    {
        try {
            Organization::where('id', $id)->update([
                "name" => $request["name"],
            ]);
            $organization = Organization::find($id);
            return $organization;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
