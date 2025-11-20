<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class GenerateDailyAttendance extends Command
{
    protected $signature = 'attendance:generate-daily';
    protected $description = 'Generate attendance record for all employees daily';

    public function handle()
    {
        $today = Carbon::today()->toDateString();

        // Ambil semua employee aktif
        $employees = Employee::with(['personal', 'activeSchedule.schedule.details.shift','employment','user'])->get();

        foreach ($employees as $employee) {

            // ❌ Skip jika employee belum memiliki user
            if (empty($employee->user_id)) {
                continue;
            }

            // Cek apakah sudah ada attendance untuk hari ini
            $exists = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->exists();

            if ($exists) {
                continue; // sudah ada → skip
            }

            // Tentukan shift hari ini
            $shift = $this->resolveShift($employee, $today);

            Attendance::create([
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id,
                'date' => $today,
                'status' => 'absent',
                'fullname' => $employee->personal->fullname,
                'shift_name' => $employee->activeSchedule->schedule_name ?? '-',
                'holiday' => $shift->holiday ?? 0,
                'schedule_in' => $shift->schedule_in ?? null,
                'schedule_out' => $shift->schedule_out ?? null,
            ]);
        }

        $this->info('Daily attendance generated successfully.');
    }

    private function resolveShift($employee, $targetDate)
    {
        $shiftLength = $employee->activeSchedule->schedule->count_detail??0;
        $target = Carbon::parse($targetDate)->startOfDay();
        $effective = Carbon::parse($employee->activeSchedule->effective_start_date)->startOfDay();
        $diffDays = $effective->diffInDays($target, false);

        if ($diffDays < 0) {
            return null;
        }

        $dayNumber = ($diffDays % $shiftLength) + 1;

        return $employee->activeSchedule->schedule
            ->details
            ->where('number', $dayNumber)
            ->first()
            ->shift;
    }
}
