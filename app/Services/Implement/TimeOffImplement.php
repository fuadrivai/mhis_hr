<?php

namespace App\Services\Implement;

use App\Models\TimeOff;
use App\Services\TimeOffService;

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
            return  TimeOff::find($id);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function post($request)
    {
        try {
            if (TimeOff::where('code', $request['code'])->exists()) {
                return response()->json([
                    "message" => "Code already exists"
                ], 409);
            }

            $timeOff = TimeOff::create([
                'code'              => $request['code'],
                'name'              => $request['name'],
                'description'       => $request['description'],
                'deduct_from_leave' => $request['deduct_from_leave'] ?? 0,
                'is_paid'           => $request['is_paid'] ?? 0,
                'need_attachement'   => $request['need_attachement'] ?? 0,
            ]);
            return $timeOff;
        } catch (\Throwable $th) {
            return response()->json(
                ["message" => $th->getMessage()],
                500
            );
        }
    }

    function put($request)
    {
        try {
            $timeOff = TimeOff::find($request['id']);
            if (!$timeOff) {
                return response()->json(["message" => "TimeOff not found"], 404);
            }

            $timeOff->update([
                'code'              => $request['code'] ?? $timeOff->code,
                'name'              => $request['name'] ?? $timeOff->name,
                'deduct_from_leave' => $request['deduct_from_leave'] ?? $timeOff->deduct_from_leave,
                'is_paid'           => $request['is_paid'] ?? $timeOff->is_paid,
                'need_attachment'   => $request['need_attachment'] ?? $timeOff->need_attachment,
            ]);

            return $timeOff;

        } catch (\Throwable $th) {
            return response()->json(
                ["message" => $th->getMessage()],
                $th->getCode() ?: 500
            );
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
