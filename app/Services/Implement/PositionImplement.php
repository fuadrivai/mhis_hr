<?php

namespace App\Services\Implement;

use App\Models\Position;
use App\Services\PositionService;

class PositionImplement implements PositionService
{
    function get()
    {
        try {
            $positions = Position::all();
            return response()->json($positions);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id) {}
    function post($request)
    {
        try {
            $postion = new Position();
            $postion->name = $request['name'];
            $postion->save();
            return response()->json($postion);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($id, $request)
    {
        try {
            Position::where('id', $id)->update([
                "name" => $request["name"],
            ]);
            $position = Position::find($id);
            return response()->json($position);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
