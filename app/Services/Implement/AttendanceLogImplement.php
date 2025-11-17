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
            $shiftForToday = $employee->activeSchedule->schedule->details->where('number', $dayNumber)->first()->shift;

            $resolved = resolveAttendanceDate($shiftForToday, $data['date']);
            $attendanceDate = $resolved['attendance_date'];

            if (!empty($data['photo'])) {
                $img = str_replace(['data:image/png;base64,', ' '], ['', '+'], $data['photo']);
                $imageName = 'attendance_' . $employee->id . '_' . time() . '.png';
                $path = storage_path('app/public/attendance_photos/' . $imageName);
                file_put_contents($path, base64_decode($img));
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
                    'check_in_photo' => $photoPath ?? null,
                ]
            );

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
            ]);

            if (!$attendance->check_in || Carbon::parse($data['date'])->lt(Carbon::parse($attendance->check_in))) {
                $attendance->update([
                    'check_in' => $data['date'],
                    'check_in_photo' => $photoPath ?? null,
                ]);
            }

            return $log->load(['attendance', 'employee.personal']);
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

            $shiftLength = $employee->activeSchedule->schedule->count_detail;
            $target = Carbon::parse($data['date'])->startOfDay();
            $effective = Carbon::parse($employee->activeSchedule->effective_start_date)->startOfDay();
            $diffDays = $effective->diffInDays($target, false);
            $dayNumber = ($diffDays % $shiftLength) + 1;
            $shiftForToday = $employee->activeSchedule->schedule->details->where('number', $dayNumber)->first()->shift;

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
            ]);

            $latest = AttendanceLog::where('attendance_id', $attendance->id)
                ->where('type', 'check_out')
                ->orderBy('clock_datetime', 'DESC')
                ->first();

            if ($latest) {
                $attendance->update([
                    'check_out' => $latest->clock_datetime,
                    'check_out_photo' => $latest->photo,
                ]);
            }

            return $log->load(['attendance', 'employee.personal']);
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
