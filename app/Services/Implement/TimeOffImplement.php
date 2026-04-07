<?php

namespace App\Services\Implement;

use App\Models\TimeOff;
use App\Services\TimeOffService;
use Illuminate\Support\Facades\DB;

class TimeOffImplement implements TimeOffService
{
    function get()
    {
        try {
            return TimeOff::all();
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function show($id)
    {
        try {
            return TimeOff::find($id);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function post($request)
    {
        DB::beginTransaction();
        try {
            // Create new TimeOff record
            $timeOff = TimeOff::create([
                'code' => $request['code'],
                'name' => $request['name'],
                'schema' => $request['schema'] ? json_decode($request['schema'], true) : null,
                'is_active' => true
            ]);

            DB::commit();
            return $timeOff;
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(["message" => $th->getMessage()], $th->getCode() ?: 500);
        }
    }

    function put($request)
    {
        DB::beginTransaction();
        try {
            $timeOff = TimeOff::find($request['id']);
            if (!$timeOff) {
                return response()->json(["message" => "TimeOff not found"], 404);
            }

            $timeOff->update([
                'code' => $request['code'],
                'name' => $request['name'],
                'schema' => isset($request['schema']) ? json_decode($request['schema'], true) : $timeOff->schema,
                'is_active' => $request['is_active'] ?? $timeOff->is_active
            ]);

            DB::commit();
            return $timeOff;
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(["message" => $th->getMessage()], $th->getCode() ?: 500);
        }
    }
    

    function delete($id)
    {
        try {
            $timeOff = TimeOff::find($id);

            if (!$timeOff) {
                return response()->json([
                    "message" => "Data not found"
                ], 404);
            }
            $timeOff->delete();
            return true;
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage()
            ], 500);
        }
    }
}
