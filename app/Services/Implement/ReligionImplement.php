<?php

namespace App\Services\Implement;

use App\Models\Religion;
use App\Services\ReligionService;

class ReligionImplement implements ReligionService
{
    function get()
    {
        try {
            $religions = Religion::all();
            return response()->json($religions);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id)
    {

        try {
            $religion = Religion::find($id);
            return response()->json($religion);
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
            return response()->json($religion);
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
            $religion = Religion::find($id);
            return response()->json($religion);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
