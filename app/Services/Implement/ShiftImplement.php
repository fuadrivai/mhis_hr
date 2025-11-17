<?php

namespace App\Services\Implement;

use App\Models\Shift;
use App\Services\ShiftService;

class ShiftImplement implements ShiftService
{
    function get()
    {
        try {
            // $shifts = Shift::all();
            // dd($shifts);
            // Log::info('Form Data:', $shifts);
            return Shift::all();
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            // Log::info('Form Data:', $th->getMessage());
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function show($id)
    {
        try {
            $shift = Shift::find($id);
            return response()->json($shift);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function post($request)
    {
        try {
            $shift = new Shift();
            $shift->name = $request['name'];
            $shift->code = $request['code'];
            $shift->shift_label = $request['shift_label'];
            $shift->schedule_in = $request['schedule_in'];
            $shift->schedule_out = $request['schedule_out'];
            $shift->break_start = $request['break_start'];
            $shift->break_end = $request['break_end'];
            $shift->is_overnight = $request['is_overnight'] ?? 0;
            $shift->show_in_request = 1;
            $shift->save();
            return response()->json($shift);
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            // Log::info('Form Data:', $th->getMessage());
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($id, $request)
    {
        try {
            Shift::where('id', $id)->update([
                "name" => $request["name"],
                "code" => $request["code"],
                "shift_label" => $request["shift_label"],
                "schedule_in" => $request["schedule_in"],
                "schedule_out" => $request["schedule_out"],
                "break_start" => $request["break_start"],
                "break_end" => $request["break_end"],
                "show_in_request" => true,
            ]);
            $shift = Shift::find($id);
            return response()->json($shift);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function delete($id) {}
}
