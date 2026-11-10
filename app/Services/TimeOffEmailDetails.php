<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;

class TimeOffEmailDetails
{
    public function build(Employee $employee, array $payload): array
    {
        $startDate = ($payload['start_date'] ?? null) ?: ($payload['date'] ?? null);
        $endDate = $payload['end_date'] ?? null;
        $startTime = $payload['start_time'] ?? null;
        $endTime = $payload['end_time'] ?? null;
        $type = $payload['type'] ?? null;

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->startOfDay() : null;

        if ($start && $end && $end->lt($start)) {
            throw new \InvalidArgumentException('The time off end date cannot be before the start date.');
        }

        $minutes = $this->elapsedMinutes($payload);
        $duration = $minutes === null ? null : $minutes . ($minutes === 1 ? ' minute' : ' minutes');
        $scheduledDays = null;
        $typeKey = is_string($type) ? str_replace([' ', '_', '-'], '', strtolower($type)) : '';
        if ($start && !$startTime && !$endTime && !in_array($typeKey, ['hourly', 'fullday'], true)) {
            $days = (new ScheduledWorkDays())->count($employee, $start, $end ?? $start);
            $scheduledDays = $days . ($days === 1 ? ' day' : ' days');
        }
        return [
            'timeoff_date' => $start ? $start->format('D, M d, Y') : null,
            'timeoff_end_date' => $end && (!$start || !$end->equalTo($start)) ? $end->format('D, M d, Y') : null,
            'timeoff_type' => $type,
            'timeoff_start_time' => $startTime,
            'timeoff_end_time' => $endTime,
            'timeoff_duration' => $duration,
            'timeoff_scheduled_days' => $scheduledDays,
        ];
    }

    public function elapsedTime(array $payload): ?string
    {
        $minutes = $this->elapsedMinutes($payload);
        return $minutes === null ? null : intdiv($minutes, 60) . 'h ' . ($minutes % 60) . 'm';
    }

    private function elapsedMinutes(array $payload): ?int
    {
        $startTime = $payload['start_time'] ?? null;
        $endTime = $payload['end_time'] ?? null;
        if (!$startTime || !$endTime) {
            return null;
        }
        if (!is_string($startTime) || !is_string($endTime)) {
            throw new \InvalidArgumentException('Invalid time off time.');
        }

        try {
            $startDate = ($payload['start_date'] ?? null) ?: ($payload['date'] ?? null);
            $endDate = ($payload['end_date'] ?? null) ?: $startDate;
            $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
            $end = $endDate ? Carbon::parse($endDate)->startOfDay() : null;
        } catch (\Carbon\Exceptions\InvalidFormatException $exception) {
            throw new \InvalidArgumentException('Invalid time off dates.', 0, $exception);
        }
        $from = $this->parseTime($startTime, $start);
        $to = $this->parseTime($endTime, $end);
        if ($to->lt($from)) {
            throw new \InvalidArgumentException('The time off end time cannot be before the start time.');
        }
        return (int) $from->diffInMinutes($to);
    }

    private function parseTime(string $time, ?Carbon $date): Carbon
    {
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $time)) {
            throw new \InvalidArgumentException('Invalid time off time: ' . $time);
        }

        return Carbon::parse(($date ? $date->toDateString() : '2000-01-01') . ' ' . $time);
    }
}
