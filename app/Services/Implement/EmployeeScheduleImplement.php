<?php

namespace App\Services\Implement;

use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Services\EmployeeScheduleService;
use Carbon\Carbon;

class EmployeeScheduleImplement implements EmployeeScheduleService
{
    public function get($request)
    {
        // TODO: Implement get() method.
    }

    public function show($id)
    {
        // TODO: Implement show() method.
    }

    public function post($request)
    {
        $ids = explode(',', $request['employee_id']);
        $schedule = EmployeeSchedule::whereIn('employee_id', $ids)
            ->where('effective_start_date', '>=', $request['effective_start_date'])
            ->orderBy('effective_start_date', 'desc')
            ->first();

        $date1 = Carbon::parse($request['effective_start_date']);
        $date2 = Carbon::parse($schedule->effective_start_date ?? null);
        if (isset($schedule) && ($date1->lte($date2))) {
            throw new \Exception("Effective date must be greater than " . $schedule->effectiveDate(), 400);
        }

        $data = [];
        foreach ($ids as $id) {
            $data[] = [
                'employee_id' => $id,
                'schedule_id' => $request['schedule_id'],
                'schedule_name' => $request['schedule_name'] ?? null,
                'effective_start_date' => $request['effective_start_date'],
            ];
        }

        EmployeeSchedule::insert($data);
        return ["status" => "success", "message" => "input data success"];
    }

    public function put($request)
    {
        // TODO: Implement put() method.
    }

    public function delete($id)
    {
        // TODO: Implement delete() method.
    }
}
