<?php

namespace App\Services\Implement;

use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Services\ScheduleService;

use function App\Helpers\diffTime;

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
            $schedule = Schedule::with('details')->find($id);
            return response()->json($schedule);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function post($request)
    {
        try {
            $schedule = new Schedule();
            $schedule->name = $request['name'];
            $schedule->effective_date = $request['effectiveDate'];
            $schedule->description = $request['description'];
            $schedule->ignore_national_holiday = $request['ignoreNationalHoliday'];
            $schedule->ignore_special_holiday = $request['ignoreSpeciallHoliday'];
            $schedule->ignore_company_holiday = $request['ignoreCompanylHoliday'];
            $schedule->count_detail = count($request['details']);
            $schedule->save();

            for ($i=0; $i < count($request['details']); $i++) { 
                $d = $request['details'][$i];
                $detail = new ScheduleDetail();
                $detail->schedule_id = $schedule->id;
                $detail->shift_id = $d['shift']['id'];
                $detail->shift_name = $d['shift']['name'];
                $detail->day = $d['day'];
                $detail->number = $d['number'];
                $detail->working_hour = diffTime($d['shift']['schedule_in'],$d['shift']['schedule_out']);

                if (isset($d['shift']['break_start']) && isset($d['shift']['break_end'])) {
                    $detail->break_hour = diffTime($d['shift']['break_start'],$d['shift']['break_end']);
                }else{
                    $detail->break_hour="-";
                }

                $detail->save();
            }

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
                "ignore_national_holiday" => $request['ignoreNationalHoliday'],
                "ignore_special_holiday" => $request['ignoreSpeciallHoliday'],
                "ignore_company_holiday" => $request["ignoreCompanylHoliday"],
            ]);
            ScheduleDetail::where('schedule_id',$id)->delete();
            for ($i=0; $i < count($request['details']); $i++) { 
                $d = $request['details'][$i];
                $detail = new ScheduleDetail();
                $detail->schedule_id = $id;
                $detail->shift_id = $d['shift']['id'];
                $detail->shift_name = $d['shift']['name'];
                $detail->day = $d['day'];
                $detail->number = $d['number'];
                $detail->working_hour = diffTime($d['shift']['schedule_in'],$d['shift']['schedule_out']);

                if (isset($d['shift']['break_start']) && isset($d['shift']['break_end'])) {
                    $detail->break_hour = diffTime($d['shift']['break_start'],$d['shift']['break_end']);
                }else{
                    $detail->break_hour="-";
                }

                $detail->save();
            }
            $schedule = Schedule::with('details')->find($id);
            return response()->json($schedule);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function delete($id) {}
}
