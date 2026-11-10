<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;

class ScheduledWorkDays
{
    public function count(Employee $employee, Carbon $start, Carbon $end): int
    {
        if ($end->lt($start)) {
            throw new \InvalidArgumentException('The time off end date cannot be before the start date.');
        }

        $schedules = $employee->relationLoaded('schedules')
            ? $employee->schedules
            : $employee->schedules()
                ->whereDate('effective_start_date', '<=', $end->toDateString())
                ->where(function ($query) use ($start) {
                    $query->whereNull('effective_end_date')
                        ->orWhereDate('effective_end_date', '>=', $start->toDateString());
                })
                ->with('schedule.details.shift')
                ->get();
        $overrides = $employee->relationLoaded('shiftOverrides')
            ? $employee->shiftOverrides
            : $employee->shiftOverrides()
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->with('shift')
                ->get();

        $schedules = $schedules->sort(function ($a, $b) {
            return strcmp($b->effective_start_date, $a->effective_start_date) ?: ($b->id <=> $a->id);
        });
        $overrides = $overrides->keyBy(fn ($override) => $override->date->toDateString());

        $days = 0;
        for ($date = $start->copy()->startOfDay(); $date->lte($end); $date->addDay()) {
            $override = $overrides->get($date->toDateString());
            $shift = $override ? $override->shift : null;
            if (!$shift) {
                $assignment = $schedules->first(function ($item) use ($date) {
                    return Carbon::parse($item->effective_start_date)->startOfDay()->lte($date)
                        && (!$item->effective_end_date || Carbon::parse($item->effective_end_date)->startOfDay()->gte($date));
                });
                $schedule = $assignment ? $assignment->schedule : null;
                if ($schedule && $schedule->count_detail > 0) {
                    $number = ((int) Carbon::parse($assignment->effective_start_date)->startOfDay()->diffInDays($date, false) % $schedule->count_detail) + 1;
                    $detail = $schedule->details->firstWhere('number', $number);
                    $shift = $detail ? $detail->shift : null;
                }
            }

            $name = str_replace([' ', '_', '-'], '', strtolower((string) optional($shift)->name));
            if ($shift && !$shift->holiday && !str_contains($name, 'dayoff')) {
                $days++;
            }
        }

        return $days;
    }
}
