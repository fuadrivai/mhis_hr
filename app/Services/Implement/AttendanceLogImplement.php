<?php

namespace App\Services\Implement;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Services\AttendanceLogService;
use Carbon\Carbon;
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
            $user = $data['user'];
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            $employee = Employee::with(['personal', 'activeSchedule.schedule.details.shift'])->where('user_id', $user['id'])->first();
            $shiftLength = $employee->activeSchedule->schedule->count_detail;
            $target = Carbon::parse($data['date'])->startOfDay();
            $effective = Carbon::parse($employee->activeSchedule->effective_start_date)->startOfDay();
            $diffDays = $effective->diffInDays($target, false);

            if ($diffDays < 0) {
                return response()->json(['message' => 'Your schedule is not yet active on that date'], 400);
            }
            $dayNumber = ($diffDays % $shiftLength) + 1;
            $shiftForToday =  $employee->activeSchedule->schedule->details->where('number', $dayNumber)->first();

            $today = Carbon::parse($data['date'])->toDateString();
            $time = Carbon::parse($data['date'])->toTimeString();
            // return $shiftForToday;
            $isPhoto = (isset($data['photo']) && !empty($data['photo']))|| $data['photo'] !="";
            if ($isPhoto) {
                $image = $data['photo'];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'attendance_' . $employee->id . '_' . time() . '.png';
                $path = storage_path('app/public/attendance_photos/' . $imageName);
                file_put_contents($path, base64_decode($image));
                $photoPath = 'attendance_photos/' . $imageName;
            }

            $attendance = Attendance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $today,
                ],
                [
                    'user_id' => $user['id'] ?? null,
                    'fullname' => $employee->personal->fullname,
                    'shift_name' => $employee->activeSchedule->schedule_name ?? '-',
                    'status' => 'present',
                    'holiday' => $shiftForToday->shift->holiday ? 1 : 0,
                    'schedule_in' => $shiftForToday->shift->schedule_in ?? null,
                    'schedule_out' => $shiftForToday->shift->schedule_out ?? null,
                    'check_in_photo' => $photoPath??null,
                ]
            );

            $attendanceLog= AttendanceLog::create([
                'employee_id' => $employee->id,
                'attendance_id' => $attendance->id,
                'type' => 'check_in',
                'fullname' => $attendance->fullname,
                'shift_name' => $attendance->shift_name,
                'photo' => $photoPath ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'radius' => $data['radius'] ?? null,
                'clock_datetime' => $data['date'],
                'clock_date' => $today,
                'time' => $time,
            ]);
            $carbonClockDatetime = Carbon::parse($data['date']);
            $carbonCheckIn = $attendance->check_in
                ? Carbon::parse($attendance->check_in)
                : null;

            if (!$attendance->check_in || $carbonClockDatetime->lt($carbonCheckIn)) {
                $attendance->update([
                    'check_in' => $carbonClockDatetime,
                    'check_in_photo' => $photoPath??null,
                    'check_in_latitude' => $data['latitude'] ?? null,
                    'check_in_longitude' => $data['longitude'] ?? null,
                    'check_in_radius' => $data['radius'] ?? null,
                ]);
            }
            $attendanceLog->photo = $isPhoto? asset('storage/'. $photoPath):null;
            return $attendanceLog->load(['attendance', 'employee','employee.employment','employee.personal']);
        });
    }

    public function clock_out($data)
{
    return DB::transaction(function () use ($data) {

        $user = $data['user'];
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $employee = Employee::with(['personal', 'activeSchedule.schedule.details.shift'])
            ->where('user_id', $user['id'])->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $today = Carbon::parse($data['date'])->toDateString();
        $time = Carbon::parse($data['date'])->toTimeString();

       $isPhoto = (isset($data['photo']) && !empty($data['photo']))|| $data['photo'] !="";
        if ($isPhoto) {
            $image = $data['photo'];
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'attendance_' . $employee->id . '_' . time() . '.png';
            $path = storage_path('app/public/attendance_photos/' . $imageName);
            file_put_contents($path, base64_decode($image));
            $photoPath = 'attendance_photos/' . $imageName;
        }

        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $today,
            ],
            [
                'user_id' => $user['id'],
                'fullname' => $employee->personal->fullname,
                'shift_name' => $employee->activeSchedule->schedule_name ?? '-',
                'status' => 'present',
            ]
        );

        $attendanceLog = AttendanceLog::create([
            'employee_id' => $employee->id,
            'attendance_id' => $attendance->id,
            'type' => 'check_out',
            'fullname' => $attendance->fullname,
            'shift_name' => $attendance->shift_name,
            'photo' => $photoPath ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'radius' => $data['radius'] ?? null,
            'clock_datetime' => $data['date'],
            'clock_date' => $today,
            'time' => $time,
        ]);

        $latestLog = AttendanceLog::where('attendance_id', $attendance->id)
            ->where('clock_date', $today)
            ->where('type', 'check_out')
            ->orderBy('clock_datetime', 'DESC')
            ->first();

        if ($latestLog) {
            $attendance->update([
                'check_out' => $latestLog->clock_datetime,
                'check_out_photo' => $latestLog->photo,
                'check_out_latitude' => $latestLog->latitude,
                'check_out_longitude' => $latestLog->longitude,
                'check_out_radius' => $latestLog->radius,
            ]);
        }

        $attendanceLog->photo = $isPhoto ? asset('storage/' . $photoPath) : null;

        return $attendanceLog->load(['attendance', 'employee', 'employee.employment', 'employee.personal']);
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

    function getDayNumber($targetDate, $effectiveDate, $length)
    {
        $target = Carbon::parse($targetDate)->startOfDay();    // 2025-09-18 00:00:00
        $effective = Carbon::parse($effectiveDate)->startOfDay(); // 2025-08-30 00:00:00

        $diffDays = $effective->diffInDays($target, false);
        $dayNumber = ($diffDays % $length) + 1;

        return $dayNumber;
    }
}
