<?php

namespace App\Services\Implement;

use App\Models\EmployeeTimeoffBalance;
use App\Models\EmployeeTimeoffBalanceTransaction;
use App\Models\TimeOff;
use App\Models\TimeoffBalanceGroup;
use App\Services\HourlyTimeOffBalanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HourlyTimeOffBalanceImplement implements HourlyTimeOffBalanceService
{
    public function getGroups()
    {
        return TimeoffBalanceGroup::withCount('timeoffs')->orderBy('name')->get();
    }

    public function getGroup($id)
    {
        return TimeoffBalanceGroup::with('timeoffs')->findOrFail($id);
    }

    public function saveGroup(array $attributes, $id = null)
    {
        $assignTypes = array_key_exists('timeoff_ids', $attributes);
        if (($attributes['period_type'] ?? null) === 'unlimited') {
            $attributes['maximum_hours'] = null;
        }

        return DB::transaction(function () use ($attributes, $id, $assignTypes) {
            $timeoffIds = array_values(array_unique($attributes['timeoff_ids'] ?? []));
            unset($attributes['timeoff_ids']);

            $group = $id ? TimeoffBalanceGroup::lockForUpdate()->findOrFail($id) : new TimeoffBalanceGroup();
            $group->fill($attributes);
            $group->save();

            if ($assignTypes) {
                TimeOff::where('balance_group_id', $group->id)->update(['balance_group_id' => null]);
                if ($timeoffIds) {
                    TimeOff::whereIn('id', $timeoffIds)->update(['balance_group_id' => $group->id]);
                }
            }

            return $group->load('timeoffs');
        });
    }

    public function getCurrentPeriod($balanceGroupId)
    {
        $group = TimeoffBalanceGroup::findOrFail($balanceGroupId);
        $today = Carbon::today();

        if ($group->period_type === 'yearly') {
            $start = $today->copy()->startOfYear();
            $end = $today->copy()->endOfYear();
        } elseif ($group->period_type === 'unlimited') {
            $start = Carbon::parse('1970-01-01');
            $end = null;
        } else {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->endOfMonth();
        }

        return [
            'period_start' => $start->toDateString(),
            'period_end' => $end ? $end->toDateString() : null,
        ];
    }

    public function getCurrentBalance($employeeId, $balanceGroupId)
    {
        return $this->getOrCreateCurrentBalance($employeeId, $balanceGroupId);
    }

    public function getOrCreateCurrentBalance($employeeId, $balanceGroupId)
    {
        return DB::transaction(function () use ($employeeId, $balanceGroupId) {
            $group = TimeoffBalanceGroup::findOrFail($balanceGroupId);
            $period = $this->getCurrentPeriod($group->id);
            $now = now();
            $allocated = $group->period_type === 'unlimited' ? '0.00' : $group->maximum_hours;

            $inserted = DB::table('employee_timeoff_balances')->insertOrIgnore([
                'employee_id' => $employeeId,
                'balance_group_id' => $group->id,
                'period_start' => $period['period_start'],
                'period_end' => $period['period_end'],
                'allocated_hours' => $allocated,
                'used_hours' => '0.00',
                'remaining_hours' => $allocated,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $balance = EmployeeTimeoffBalance::where('employee_id', $employeeId)
                ->where('balance_group_id', $group->id)
                ->whereDate('period_start', $period['period_start'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($inserted) {
                EmployeeTimeoffBalanceTransaction::create([
                    'employee_id' => $employeeId,
                    'balance_id' => $balance->id,
                    'transaction_type' => 'allocation',
                    'hours' => $allocated,
                    'description' => 'Initial allocation for ' . $group->name . '.',
                ]);
            }

            return $balance;
        });
    }

    public function checkAvailability($employeeId, $balanceGroupId, $hours)
    {
        $requestedCents = $this->hoursToCents($hours);
        $group = TimeoffBalanceGroup::findOrFail($balanceGroupId);
        if (!$group->is_active) {
            throw ValidationException::withMessages([
                'timeoff_id' => 'This hourly time off balance group is inactive. Please contact HR.',
            ]);
        }

        if ($group->period_type === 'unlimited') {
            return true;
        }

        $balance = $this->getOrCreateCurrentBalance($employeeId, $balanceGroupId);
        $remainingCents = $this->hoursToCents($balance->remaining_hours, true);

        if ($requestedCents > $remainingCents) {
            throw ValidationException::withMessages([
                'timeoff_id' => sprintf(
                    'Your remaining %s balance is %s hours. You requested %s hours.',
                    $group->name,
                    $this->centsToHours($remainingCents),
                    $this->centsToHours($requestedCents)
                ),
            ]);
        }

        return true;
    }

    public function consume($employeeId, $balanceGroupId, $approvalRequestId, $timeoffId, $hours)
    {
        $requestedCents = $this->hoursToCents($hours);

        return DB::transaction(function () use ($employeeId, $balanceGroupId, $approvalRequestId, $timeoffId, $requestedCents) {
            $existingUsage = EmployeeTimeoffBalanceTransaction::where('approval_request_id', $approvalRequestId)
                ->where('transaction_type', 'usage')
                ->first();
            if ($existingUsage) {
                return $existingUsage->balance;
            }

            $group = TimeoffBalanceGroup::findOrFail($balanceGroupId);
            $balance = $this->getOrCreateCurrentBalance($employeeId, $balanceGroupId);
            $balance = EmployeeTimeoffBalance::whereKey($balance->id)->lockForUpdate()->firstOrFail();

            $existingUsage = EmployeeTimeoffBalanceTransaction::where('approval_request_id', $approvalRequestId)
                ->where('transaction_type', 'usage')
                ->first();
            if ($existingUsage) {
                return $existingUsage->balance;
            }

            $remainingCents = $this->hoursToCents($balance->remaining_hours, true);
            if ($group->period_type !== 'unlimited' && $requestedCents > $remainingCents) {
                throw ValidationException::withMessages([
                    'timeoff_id' => sprintf(
                        'Your remaining %s balance is %s hours. You requested %s hours.',
                        $group->name,
                        $this->centsToHours($remainingCents),
                        $this->centsToHours($requestedCents)
                    ),
                ]);
            }

            $usedCents = $this->hoursToCents($balance->used_hours, true) + $requestedCents;
            $remainingCents = $group->period_type === 'unlimited'
                ? 0
                : $remainingCents - $requestedCents;

            $balance->update([
                'used_hours' => $this->centsToHours($usedCents),
                'remaining_hours' => $this->centsToHours($remainingCents),
            ]);

            EmployeeTimeoffBalanceTransaction::create([
                'employee_id' => $employeeId,
                'balance_id' => $balance->id,
                'approval_request_id' => $approvalRequestId,
                'timeoff_id' => $timeoffId,
                'transaction_type' => 'usage',
                'hours' => $this->centsToHours(-$requestedCents),
                'description' => 'Approved hourly time off request #' . $approvalRequestId . '.',
            ]);

            return $balance->fresh();
        });
    }

    public function reverse($employeeId, $approvalRequestId, $timeoffId = null)
    {
        return DB::transaction(function () use ($employeeId, $approvalRequestId, $timeoffId) {
            $usage = EmployeeTimeoffBalanceTransaction::where('employee_id', $employeeId)
                ->where('approval_request_id', $approvalRequestId)
                ->where('transaction_type', 'usage')
                ->when($timeoffId, function ($query) use ($timeoffId) {
                    $query->where('timeoff_id', $timeoffId);
                })
                ->first();

            if (!$usage || EmployeeTimeoffBalanceTransaction::where('approval_request_id', $approvalRequestId)
                ->where('transaction_type', 'reversal')->exists()) {
                return $usage ? $usage->balance : null;
            }

            $balance = EmployeeTimeoffBalance::whereKey($usage->balance_id)->lockForUpdate()->firstOrFail();
            if (EmployeeTimeoffBalanceTransaction::where('approval_request_id', $approvalRequestId)
                ->where('transaction_type', 'reversal')->exists()) {
                return $balance;
            }

            $group = TimeoffBalanceGroup::findOrFail($balance->balance_group_id);
            $reversedCents = abs($this->hoursToCents($usage->hours, true, true));
            $usedCents = max(0, $this->hoursToCents($balance->used_hours, true) - $reversedCents);
            $remainingCents = $group->period_type === 'unlimited'
                ? 0
                : $this->hoursToCents($balance->remaining_hours, true) + $reversedCents;

            $balance->update([
                'used_hours' => $this->centsToHours($usedCents),
                'remaining_hours' => $this->centsToHours($remainingCents),
            ]);

            EmployeeTimeoffBalanceTransaction::create([
                'employee_id' => $employeeId,
                'balance_id' => $balance->id,
                'approval_request_id' => $approvalRequestId,
                'timeoff_id' => $usage->timeoff_id,
                'transaction_type' => 'reversal',
                'hours' => $this->centsToHours($reversedCents),
                'description' => 'Reversal for hourly time off request #' . $approvalRequestId . '.',
            ]);

            return $balance->fresh();
        });
    }

    public function adjust($employeeId, $balanceGroupId, $hours, $description)
    {
        $deltaCents = $this->hoursToCents($hours, false, true);

        return DB::transaction(function () use ($employeeId, $balanceGroupId, $deltaCents, $description) {
            $group = TimeoffBalanceGroup::findOrFail($balanceGroupId);
            if ($group->period_type === 'unlimited') {
                throw ValidationException::withMessages([
                    'hours' => 'Unlimited balance groups do not support balance adjustments.',
                ]);
            }

            $balance = $this->getOrCreateCurrentBalance($employeeId, $balanceGroupId);
            $balance = EmployeeTimeoffBalance::whereKey($balance->id)->lockForUpdate()->firstOrFail();
            $allocatedCents = $this->hoursToCents($balance->allocated_hours, true) + $deltaCents;
            $remainingCents = $this->hoursToCents($balance->remaining_hours, true) + $deltaCents;
            if ($allocatedCents < $this->hoursToCents($balance->used_hours, true) || $remainingCents < 0) {
                throw ValidationException::withMessages([
                    'hours' => 'The adjustment cannot reduce the balance below hours already used.',
                ]);
            }

            $balance->update([
                'allocated_hours' => $this->centsToHours($allocatedCents),
                'remaining_hours' => $this->centsToHours($remainingCents),
            ]);

            EmployeeTimeoffBalanceTransaction::create([
                'employee_id' => $employeeId,
                'balance_id' => $balance->id,
                'transaction_type' => 'adjustment',
                'hours' => $this->centsToHours($deltaCents),
                'description' => $description,
            ]);

            return $balance->fresh();
        });
    }

    public function getTransactions($employeeId, $balanceGroupId, $periodStart = null)
    {
        return EmployeeTimeoffBalanceTransaction::with('timeoff', 'approvalRequest')
            ->where('employee_id', $employeeId)
            ->whereHas('balance', function ($query) use ($balanceGroupId, $periodStart) {
                $query->where('balance_group_id', $balanceGroupId);
                if ($periodStart) {
                    $query->whereDate('period_start', $periodStart);
                }
            })
            ->latest()
            ->get();
    }

    public function getGroupBalances($balanceGroupId)
    {
        $period = $this->getCurrentPeriod($balanceGroupId);
        return EmployeeTimeoffBalance::with('employee.personal')
            ->where('balance_group_id', $balanceGroupId)
            ->whereDate('period_start', $period['period_start'])
            ->orderBy('employee_id')
            ->get();
    }

    public function getRequestedHours(array $payload)
    {
        if (array_key_exists('hours', $payload) && $payload['hours'] !== '') {
            return $this->centsToHours($this->hoursToCents($payload['hours']));
        }

        $startTime = data_get($payload, 'start_time');
        $endTime = data_get($payload, 'end_time');
        if (!$startTime || !$endTime) {
            throw ValidationException::withMessages([
                'dynamic_fields' => 'Hourly time off requires positive hours or both a start time and an end time.',
            ]);
        }

        try {
            $startDate = data_get($payload, 'start_date') ?? data_get($payload, 'date') ?? now()->toDateString();
            $endDate = data_get($payload, 'end_date') ?? $startDate;
            $start = Carbon::parse($startDate . ' ' . $startTime);
            $end = Carbon::parse($endDate . ' ' . $endTime);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'dynamic_fields' => 'The hourly time off start or end time is invalid.',
            ]);
        }

        if ($end->lte($start)) {
            throw ValidationException::withMessages([
                'dynamic_fields' => 'The end time must be after the start time.',
            ]);
        }

        $minutes = $start->diffInMinutes($end);
        return $this->centsToHours((int) round($minutes * 100 / 60));
    }

    private function hoursToCents($hours, $allowZero = false, $allowNegative = false)
    {
        if (!is_numeric($hours)) {
            throw ValidationException::withMessages(['hours' => 'Hours must be a valid number.']);
        }

        $cents = (int) round((float) $hours * 100);
        if ((!$allowNegative && $cents < 0) || (!$allowZero && $cents === 0)) {
            throw ValidationException::withMessages(['hours' => 'Hours must be greater than zero.']);
        }

        return $cents;
    }

    private function centsToHours($cents)
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
