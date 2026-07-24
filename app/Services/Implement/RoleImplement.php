<?php

namespace App\Services\Implement;

use App\Models\Role;
use App\Services\RoleService;

class RoleImplement implements RoleService
{
    function get()
    {
        try {
            return Role::all();
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id)
    {

        try {
            return Role::find($id);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function post($request)
    {
        try {
            $role = new Role();
            $role->name = $request['name'];
            $role->description = $request['description'];
            $role->save();
            return $role;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function changeUserRole($request)
    {
        try {
            $role = Role::find($request['role_id']);
            $user = $role->users()->where('id', $request['user_id'])->first();
            if ($user) {
                $role->users()->detach($user);
                return response()->json(["message" => "User removed from role"], 200);
            } else {
                $role->users()->attach($request['user_id']);
                return response()->json(["message" => "User added to role"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($request)
    {
        try {
            Role::where('id', $request['id'])->update([
                "name" => $request["name"],
                "description" => $request["description"]
            ]);
            return Role::find($request['id']);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
