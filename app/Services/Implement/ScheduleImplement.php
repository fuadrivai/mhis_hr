<?php

namespace App\Services\Implement;

use App\Models\Schedule;
use App\Services\ScheduleService;

class ScheduleImplement implements ScheduleService
{
    function get()
    {
        try {
            $schedules = Schedule::all();
            // dd($shifts);
            // Log::info('Form Data:', $shifts);
            return response()->json($schedules);
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            // Log::info('Form Data:', $th->getMessage());
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function show($id)
    {
        try {
            $schedules = Schedule::find($id);
            return response()->json($schedules);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function post($request)
    {
        try {
            $schedule = new Schedule();
            $schedule->name = $request['name'];
            $schedule->effective_date = $request['effective_date'];
            $schedule->description = $request['description'];
            $schedule->ignore_national_holiday = $request['ignore_national_holiday'];
            $schedule->ignore_special_holiday = $request['ignore_special_holiday'];
            $schedule->ignore_company_holiday = $request['ignore_company_holiday'];
            $schedule->save();
            return response()->json($schedule);
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            // Log::info('Form Data:', $th->getMessage());
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($id, $request)
    {
        try {
            Schedule::where('id', $id)->update([
                "name" => $request["name"],
                "effective_date" => $request["effective_date"],
                "description" => $request["description"],
                "ignore_national_holiday" => $request["ignore_national_holiday"],
                "ignore_special_holiday" => $request["ignore_special_holiday"],
                "ignore_company_holiday" => $request["ignore_company_holiday"],
            ]);
            $schedule = Schedule::find($id);
            return response()->json($schedule);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function delete($id) {}
}
