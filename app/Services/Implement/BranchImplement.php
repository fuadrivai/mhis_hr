<?php

namespace App\Services\Implement;

use App\Models\Branch;
use App\Services\BranchService;

class BranchImplement implements BranchService
{
    function get()
    {
        try {
            return Branch::all();
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id) {}
    function post($request)
    {
        try {
            $branch = new Branch();
            $branch->name = $request['name'];
            $branch->code = $request['code'];
            $branch->description = $request['description'];
            $branch->save();
            return response()->json($branch);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($id, $request)
    {
        try {
            Branch::where('id', $id)->update([
                "name" => $request["name"],
                "code" => $request["code"],
                "description" => $request["description"],
            ]);
            $branch = Branch::find($id);
            return response()->json($branch);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
