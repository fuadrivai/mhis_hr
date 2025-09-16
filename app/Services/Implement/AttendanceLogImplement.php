<?php

namespace App\Services\Implement;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Services\AttendanceLogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceLogImplement implements AttendanceLogService
{
    public function get($id)
    {
        // TODO: Implement get() method.
    }

    public function show($id)
    {
        // TODO: Implement show() method.
    }
    public function showByEmployeeId($employeeId)
    {
        // TODO: Implement showByEmployeeId() method.
    }

    public function clock_in($data)
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $employee = Employee::with(['personal', 'schedules'])->where('user_id', $user->id)->first();
            $today = Carbon::today()->toDateString();

            $attendance = Attendance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $today,
                ],
                [
                    'user_id' => $user->id ?? null,
                    'fullname' => $employee->personal->fullname ?? $employee->name,
                    'shift_name' => $employee->schedule->name ?? '-',
                    'status' => 'present',
                    'holiday' => $data['holiday'] ?? false,
                    'schedule_in' => $data['schedule_in'] ?? null,
                    'schedule_out' => $data['schedule_out'] ?? null,
                ]
            );

            $now = Carbon::now();

            AttendanceLog::create([
                'employee_id' => $employee->id,
                'attendance_id' => $attendance->id,
                'type' => 'check_in',
                'fullname' => $attendance->fullname,
                'shift_name' => $attendance->shift_name,
                'photo' => $data['photo'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'radius' => $data['radius'] ?? null,
                'clock_datetime' => $now,
            ]);

            // if (!$attendance->check_in || $now->lt(Carbon::parse($attendance->check_in))) {
            //     $attendance->update([
            //         'check_in' => $now,
            //         'check_in_photo' => $data['photo'] ?? null,
            //         'check_in_latitude' => $data['latitude'] ?? null,
            //         'check_in_longitude' => $data['longitude'] ?? null,
            //         'check_in_radius' => $data['radius'] ?? null,
            //     ]);
            // }
            return $attendance;
        });
    }

    public function clock_out($data)
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::findOrFail($data['employee_id']);
            $today = Carbon::today()->toDateString();

            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            $now = Carbon::now();

            if (!$attendance) {
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'user_id' => $data['user_id'] ?? null,
                    'date' => $today,
                    'fullname' => $employee->personal->fullname ?? $employee->name,
                    'shift_name' => $employee->schedule->name ?? '-',
                    'status' => 'present',
                    'holiday' => $data['holiday'] ?? false,
                    'schedule_in' => $data['schedule_in'] ?? null,
                    'schedule_out' => $data['schedule_out'] ?? null,
                ]);
            }

            AttendanceLog::create([
                'employee_id' => $employee->id,
                'attendance_id' => $attendance->id,
                'type' => 'check_out',
                'fullname' => $attendance->fullname,
                'shift_name' => $attendance->shift_name,
                'photo' => $data['photo'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'radius' => $data['radius'] ?? null,
                'clock_datetime' => $now,
            ]);

            if (!$attendance->check_out || $now->gt(Carbon::parse($attendance->check_out))) {
                $attendance->update([
                    'check_out' => $now,
                    'check_out_photo' => $data['photo'] ?? null,
                    'check_out_latitude' => $data['latitude'] ?? null,
                    'check_out_longitude' => $data['longitude'] ?? null,
                    'check_out_radius' => $data['radius'] ?? null,
                ]);
            }

            return $attendance;
        });
    }
    public function put($data)
    {
        // TODO: Implement put() method.
    }

    public function delete($id)
    {
        // TODO: Implement delete() method.
    }
}
