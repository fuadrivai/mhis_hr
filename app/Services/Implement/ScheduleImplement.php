<?php

namespace App\Services\Implement;

use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use function App\Helpers\diffTime;

class ScheduleImplement implements ScheduleService
{
    function get()
    {
        try {
            $schedules = Schedule::all();
            return $schedules;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function show($id)
    {
        try {
            $schedule = Schedule::with('details')->find($id);
            return $schedule;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function post($request)
    {
        try {
            $schedule = null;
            DB::transaction(function () use ($request, &$schedule) {
                $schedule = new Schedule();
                $schedule->name = $request['name'];
                $schedule->effective_date = $request['effectiveDate'];
                $schedule->description = $request['description'];
                $schedule->ignore_national_holiday = $request['ignoreNationalHoliday'];
                $schedule->ignore_special_holiday = $request['ignoreSpeciallHoliday'];
                $schedule->ignore_company_holiday = $request['ignoreCompanylHoliday'];
                $schedule->count_detail = count($request['details']);
                $schedule->save();

                $details = [];
                foreach ($request['details'] as $d) {
                    $details[] = [
                        'schedule_id'   => $schedule->id,
                        'shift_id'      => $d['shift']['id'],
                        'shift_name'    => $d['shift']['name'],
                        'day'           => $d['day'],
                        'number'        => $d['number'],
                        'working_hour'  => diffTime($d['shift']['schedule_in'], $d['shift']['schedule_out']),
                        'break_hour'    => (isset($d['shift']['break_start']) && isset($d['shift']['break_end']))
                            ? diffTime($d['shift']['break_start'], $d['shift']['break_end'])
                            : "-",
                    ];
                }
                if (!empty($details)) {
                    ScheduleDetail::insert($details);
                }
            });
            return response()->json($schedule);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($id, $request)
    {
        try {
            DB::transaction(function () use ($id, $request) {
                Schedule::where('id', $id)->update([
                    "name" => $request["name"],
                    "effective_date" => $request['effectiveDate'],
                    "description" => $request["description"],
                    "ignore_national_holiday" => $request['ignoreNationalHoliday'],
                    "ignore_special_holiday" => $request['ignoreSpeciallHoliday'],
                    "ignore_company_holiday" => $request["ignoreCompanylHoliday"],
                    "count_detail" => count($request['details']),
                ]);
                ScheduleDetail::where('schedule_id', $id)->delete();
                $details = [];
                foreach ($request['details'] as $d) {
                    $details[] = [
                        'schedule_id'   => $id,
                        'shift_id'      => $d['shift']['id'],
                        'shift_name'    => $d['shift']['name'],
                        'day'           => $d['day'],
                        'number'        => $d['number'],
                        'working_hour'  => diffTime($d['shift']['schedule_in'], $d['shift']['schedule_out']),
                        'break_hour'    => (isset($d['shift']['break_start']) && isset($d['shift']['break_end']))
                            ? diffTime($d['shift']['break_start'], $d['shift']['break_end'])
                            : "-",
                    ];
                }
                if (!empty($details)) {
                    ScheduleDetail::insert($details);
                }
            });
            $schedule = Schedule::with('details')->find($id);
            return response()->json($schedule);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function delete($id) {}
}
