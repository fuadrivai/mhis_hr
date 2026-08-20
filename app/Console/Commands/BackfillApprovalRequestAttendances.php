<?php

namespace App\Console\Commands;

use App\Models\ApprovalRequest;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Console\Command;

use function App\Helpers\prepareAttendance;

class BackfillApprovalRequestAttendances extends Command
{
    protected $signature = 'attendance:backfill-approval-requests';

    protected $description = 'Backfill approved approval requests to attendance';

    public function handle()
    {
        $requests = ApprovalRequest::with([
            'data',
            'requester.personal',
            'requester.activeSchedule',
        ])
        ->where('status', 'approved')
        ->whereNotNull('timeoff_id')
        ->get();

        foreach ($requests as $request) {

            if (!$request->data) {
                $this->warn("Request #{$request->id} tidak memiliki data.");
                continue;
            }

            $startDate = data_get($request->data->payload ?? [], 'start_date');
            $endDate = data_get($request->data->payload ?? [], 'end_date')?? $startDate;
            $dateDiff = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

            for ($i = 0; $i < $dateDiff; $i++){
                $date = Carbon::parse($startDate)->addDays($i)->toDateString();
                $attendance = Attendance::where('employee_id', $request->requester_employee_id)
                    ->where('date', $date)
                    ->first();
                if ($attendance) {
                    $request->attendances()->syncWithoutDetaching([$attendance->id]);
                } else {
                    $_requester = Employee::with(['personal','user', 'activeSchedule'])->where( 'id', $request->requester_employee_id)->first();
                    [$attendance, $attendanceDate] = prepareAttendance($_requester, $_requester->user, Carbon::parse($date));
                    $request->attendances()->syncWithoutDetaching([$attendance->id]);
                }
            }
        }

        $this->info('Backfill completed.');

        return Command::SUCCESS;
    }
}
