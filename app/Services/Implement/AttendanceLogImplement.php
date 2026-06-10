<?php

namespace App\Services\Implement;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Services\AttendanceLogService;
use Illuminate\Support\Facades\DB;

use function App\Helpers\createAttendanceLog;
use function App\Helpers\getEmployee;
use function App\Helpers\handlePhotoAndFaceRecognition;
use function App\Helpers\prepareAttendance;
use function App\Helpers\updateAttendanceCheckIn;
use function App\Helpers\validateLocation;
use function App\Helpers\validateUser;

class AttendanceLogImplement implements AttendanceLogService
{
    public function get($id)
    {
        // TODO: Implement get() method.
    }
     public function getCurrent($request)
    {
        $employee = Employee::where('user_id', $request['user']['id'])->first();
        $today = now()->startOfDay();
        $tomorrow = now()->endOfDay();

        return AttendanceLog::where('employee_id', $employee->id)
            ->whereBetween('clock_datetime', [$today, $tomorrow])
            ->get();
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
            $user = validateUser($data);
            $employee = getEmployee($user->id);
            validateLocation($employee, $data); //it's to validate location of employee.
            [$attendance, $attendanceDate] = prepareAttendance($employee,$user,$data['date']);
            if (!$attendance) {
                throw new \Exception('Attendance not found', 404);
            }
            $photoPath = null;
            $photoPath = handlePhotoAndFaceRecognition($employee,$data['photo'] ?? null); // it's to validate face recognation of the employee.
            $log = createAttendanceLog($employee,$attendance,$attendanceDate,$photoPath,$data);
            updateAttendanceCheckIn($attendance,$photoPath,$data);
            $log->photo = !empty($data['photo']) ? asset('storage/' . $photoPath) : null;
            return $log->load(['attendance', 'employee.personal', 'employee.employment']);
        });
    }

    public function clock_out($data)
    {
        return DB::transaction(function () use ($data) {
            $user = validateUser($data);
            $employee = getEmployee($user->id);
            validateLocation($employee, $data);
            [$attendance, $attendanceDate] = prepareAttendance($employee,$user,$data['date']);
            if (!$attendance) {
                throw new \Exception('Attendance not found', 404);
            }
            $photoPath = handlePhotoAndFaceRecognition($employee,$data['photo'] ?? null);
            $log = createAttendanceLog($employee,$attendance,$attendanceDate,$photoPath,$data,'check_out');
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
}
