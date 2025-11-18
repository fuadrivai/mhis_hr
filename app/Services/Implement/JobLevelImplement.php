<?php

namespace App\Services\Implement;

use App\Models\JobLevel;
use App\Models\Position;
use App\Services\JobLevelService;

class JobLevelImplement implements JobLevelService
{
    function get()
    {
        try {
            $levels = JobLevel::all();
            return $levels;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id) {}
    function post($request)
    {
        try {
            $level = new JobLevel();
            $level->name = $request['name'];
            $level->save();
            return $level;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }

    function put($id, $request)
    {
        try {
            JobLevel::where('id', $id)->update([
                "name" => $request["name"],
            ]);
            $level = JobLevel::find($id);
            return $level;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
