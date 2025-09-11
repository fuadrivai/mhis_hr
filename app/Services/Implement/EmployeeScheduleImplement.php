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
        $schedule = EmployeeSchedule::where('employee_id', $request['employee_id'])
            ->where('effective_start_date', '<=', $request['effective_start_date'])
            ->orderBy('effective_start_date', 'desc')
            ->first();

        if ($schedule && Carbon::parse($request['effective_start_date'])->lte(Carbon::parse($schedule->effective_start_date))) {
            throw new \Exception("Effective date must be greater than " . $schedule->effectiveDate(), 400);
        } else {
            $employeeSchedule = new EmployeeSchedule();
            $employeeSchedule->employee_id = $request['employee_id'];
            $employeeSchedule->schedule_id = $request['schedule_id'];
            $employeeSchedule->schedule_name = $request['schedule_name'] ?? null;
            $employeeSchedule->effective_start_date = $request['effective_start_date'];
            $employeeSchedule->save();
            return $employeeSchedule;
        }
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
