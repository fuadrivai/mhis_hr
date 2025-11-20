<?php

namespace App\Services\Implement;

use App\Models\Religion;
use App\Services\ReligionService;

class ReligionImplement implements ReligionService
{
    function get()
    {
        try {
            return Religion::all();
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id)
    {

        try {
            return Religion::find($id);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function post($request)
    {
        try {
            $religion = new Religion();
            $religion->name = $request['name'];
            $religion->save();
            return $religion;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($id, $request)
    {
        try {
            Religion::where('id', $id)->update([
                "name" => $request["name"],
            ]);
            return Religion::find($id);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
