<?php

namespace App\Services\Implement;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Services\AttendanceLogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use function App\Helpers\resolveAttendanceDate;

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

            $employee = Employee::with(['personal', 'activeSchedule.schedule.details.shift'])
                ->where('user_id', $user['id'])
                ->first();

            if (!$employee) {
                return response()->json(['message' => 'Employee not found'], 404);
            }

            $shiftLength = $employee->activeSchedule->schedule->count_detail;
            $target = Carbon::parse($data['date'])->startOfDay();
            $effective = Carbon::parse($employee->activeSchedule->effective_start_date)->startOfDay();
            $diffDays = $effective->diffInDays($target, false);

            if ($diffDays < 0) {
                return response()->json(['message' => 'Your schedule is not yet active on that date'], 400);
            }

            $dayNumber = ($diffDays % $shiftLength) + 1;
            $shiftForToday = $employee->activeSchedule->schedule->details
                ->where('number', $dayNumber)
                ->first()
                ->shift;

            $resolved = resolveAttendanceDate($shiftForToday, $data['date']);
            $attendanceDate = $resolved['attendance_date'];

            // $attendance = Attendance::where('employee_id', $employee->id)
            //     ->where('date', $attendanceDate)
            //     ->first();
            $attendance = Attendance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $attendanceDate,
                ],
                [
                    'user_id' => $user['id'] ?? null,
                    'fullname' => $employee->personal->fullname,
                    'shift_name' => $employee->activeSchedule->schedule_name ?? '-',
                    'status' => 'present',
                    'holiday' => $shiftForToday->holiday ? 1 : 0,
                    'schedule_in' => $resolved['schedule_in'] ?? null,
                    'schedule_out' => $resolved['schedule_out'] ?? null,
                ]
            );

            if (!$attendance) {
                return response()->json([
                    'message' => 'Attendance not generated yet. Please contact your admin.'
                ], 400);
            }

            if (!empty($data['photo'])) {
                $img = str_replace(['data:image/png;base64,', ' '], ['', '+'], $data['photo']);
                $imageName = 'attendance_' . $employee->id . '_' . time() . '.png';
                $path = storage_path('app/public/attendance_photos/' . $imageName);
                file_put_contents($path, base64_decode($img));
                $photoPath = 'attendance_photos/' . $imageName;
            }

            $log = AttendanceLog::create([
                'employee_id' => $employee->id,
                'attendance_id' => $attendance->id,
                'type' => 'check_in',
                'fullname' => $attendance->fullname,
                'shift_name' => $attendance->shift_name,
                'photo' => $photoPath ?? null,
                'clock_datetime' => $data['date'],
                'clock_date' => $attendanceDate,
                'time' => Carbon::parse($data['date'])->format('H:i:s'),
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'radius' => $data['radius'] ?? null,
            ]);

            if (
                !$attendance->check_in ||
                Carbon::parse($data['date'])->lt(Carbon::parse($attendance->check_in))
            ) {
                $attendance->update([
                    'check_in' => $data['date'],
                    'status' => 'present',
                    'check_in_photo' => $photoPath ?? null,
                    'check_in_latitude' => $data['latitude'] ?? null,
                    'check_in_longitude' => $data['longitude'] ?? null,
                    'check_in_radius' => $data['radius'] ?? null,
                ]);
            }

            $log->photo = !empty($data['photo']) ? asset('storage/' . $photoPath) : null;

            return $log->load(['attendance', 'employee.personal', 'employee.employment']);
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
            ->where('user_id', $user['id'])
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        if ($employee->user_id == null) {
            return response()->json(['message' => 'Employee does not have a user account'], 400);
        }

        $shiftLength = $employee->activeSchedule->schedule->count_detail;
        $target = Carbon::parse($data['date'])->startOfDay();
        $effective = Carbon::parse($employee->activeSchedule->effective_start_date)->startOfDay();
        $diffDays = $effective->diffInDays($target, false);

        if ($diffDays < 0) {
            return response()->json(['message' => 'Your schedule is not yet active on that date'], 400);
        }

        $dayNumber = ($diffDays % $shiftLength) + 1;
        $shiftForToday = $employee->activeSchedule->schedule->details
            ->where('number', $dayNumber)
            ->first()
            ->shift;

        $resolved = resolveAttendanceDate($shiftForToday, $data['date']);
        $attendanceDate = $resolved['attendance_date'];

        if (!empty($data['photo'])) {
            $img = str_replace(['data:image/png;base64,', ' '], ['', '+'], $data['photo']);
            $imageName = 'attendance_' . $employee->id . '_' . time() . '.png';
            file_put_contents(storage_path('app/public/attendance_photos/' . $imageName), base64_decode($img));
            $photoPath = 'attendance_photos/' . $imageName;
        }

        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $attendanceDate,
            ],
            [
                'user_id' => $user['id'],
                'fullname' => $employee->personal->fullname,
                'shift_name' => $employee->activeSchedule->schedule_name ?? '-',
                'holiday' => $shiftForToday->holiday ?? 0,
                'schedule_in' => $resolved['schedule_in'],
                'schedule_out' => $resolved['schedule_out'],
            ]
        );

        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'attendance_id' => $attendance->id,
            'type' => 'check_out',
            'fullname' => $attendance->fullname,
            'shift_name' => $attendance->shift_name,
            'photo' => $photoPath ?? null,
            'clock_datetime' => $data['date'],
            'clock_date' => $attendanceDate,
            'time' => Carbon::parse($data['date'])->format('H:i:s'),
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'radius' => $data['radius'] ?? null,
        ]);

        $attendance->update([
            'check_out' => $log->clock_datetime,
            'check_out_photo' => $log->photo,
            'status' => 'present',
            'check_out_latitude' => $data['latitude'] ?? null,
            'check_out_longitude' => $data['longitude'] ?? null,
            'check_out_radius' => $data['radius'] ?? null,
        ]);

        $log->photo = !empty($data['photo']) ? asset('storage/' . $photoPath) : null;

        return $log->load(['attendance', 'employee.personal', 'employee.employment']);
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
        $target = Carbon::parse($targetDate)->startOfDay();
        $effective = Carbon::parse($effectiveDate)->startOfDay();

        $diffDays = $effective->diffInDays($target, false);
        $dayNumber = ($diffDays % $length) + 1;

        return $dayNumber;
    }
}
