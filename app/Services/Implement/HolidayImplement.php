<?php

namespace App\Services\Implement;

use App\Models\Holiday;
use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\EmployeeShiftOverride;
use App\Models\LeaveAllocation;
use App\Models\Shift;
use App\Models\TimeOff;
use App\Services\HolidayService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HolidayImplement implements HolidayService
{
    function get()
    {
        $year = now()->year;

        return [
            'government' => Holiday::whereYear('date', $year)
                ->where('type', Holiday::TYPE_GOVERNMENT)
                ->orderBy('date'),
            'company' => Holiday::whereYear('date', $year)
                ->where('type', Holiday::TYPE_COMPANY)
                ->with('branch')
                ->orderBy('date'),
            'school' => Holiday::whereYear('date', $year)
                ->where('type', Holiday::TYPE_SCHOOL)
                ->with('branch')
                ->orderBy('date'),
        ];
    }
    function show($id)
    {
    }
    function post($request)
    {
        $type = $request->input('type');
        $branchId = $request->input('branch_id') ?: null;
        $category = $type === Holiday::TYPE_SCHOOL ? $request->input('category') : null;
        $name = $request->input('name');
        $dates = [];

        foreach (CarbonPeriod::create($request->input('start_date'), $request->input('end_date')) as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        return DB::transaction(function () use ($request, $type, $branchId, $category, $name, $dates) {
            foreach ($dates as $date) {
                $duplicateQuery = Holiday::whereDate('date', $date)
                    ->where('type', $type);

                if ($type === Holiday::TYPE_SCHOOL) {
                    $duplicateQuery->where('category', $category);
                }

                if ($branchId === null) {
                    $duplicateQuery->whereNull('branch_id');
                } else {
                    $duplicateQuery->where('branch_id', $branchId);
                }

                if ($duplicateQuery->exists()) {
                    throw ValidationException::withMessages([
                        'start_date' => "A holiday already exists for {$date} with this type, category, and branch.",
                    ]);
                }
            }

            $createdHolidays = collect($dates)->map(function ($date) use ($request, $type, $branchId, $category, $name) {
                return Holiday::create([
                    'type' => $type,
                    'category' => $category,
                    'description' => $request->input('description'),
                    'branch_id' => $branchId,
                    'date' => $date,
                    'name' => $name,
                    'is_active' => true,
                ])->load('branch');
            });

            $this->reconcileDates($dates);

            return $createdHolidays;
        });
    }
    public function reconcileDates(array $dates)
    {
        $dates = collect($dates)
            ->filter()
            ->map(function ($date) {
                return Carbon::parse($date)->toDateString();
            })
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return;
        }

        $specialLeaveTimeOffId = TimeOff::where('name', 'Special Leave')->value('id');
        $activeAcademicYearId = AcademicYear::where('is_active', true)->value('id');

        foreach ($dates as $date) {
            $academicYearId = AcademicYear::whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->value('id') ?: $activeAcademicYearId;

            $holidays = Holiday::whereDate('date', $date)
                ->where('is_active', true)
                ->orderBy('id')
                ->get(['id', 'type', 'category', 'branch_id']);

            $employees = Employee::where('is_active', true)
                ->whereHas('schedules', function ($query) use ($date) {
                    $query->whereDate('effective_start_date', '<=', $date)
                        ->where(function ($query) use ($date) {
                            $query->whereNull('effective_end_date')
                                ->orWhereDate('effective_end_date', '>=', $date);
                        });
                })
                ->with('employment')
                ->get();

            if ($employees->isEmpty()) {
                continue;
            }

            $employeeIds = $employees->pluck('id');
            $overrides = EmployeeShiftOverride::whereDate('date', $date)
                ->whereIn('employee_id', $employeeIds)
                ->get()
                ->keyBy('employee_id');

            $specialLeaveEmployeeIds = collect();
            $hasNewAcademicYearHoliday = $holidays->contains(function ($holiday) {
                return $holiday->type === Holiday::TYPE_SCHOOL
                    && $holiday->category === Holiday::CATEGORY_NEW_ACADEMIC_YEAR;
            });

            if ($hasNewAcademicYearHoliday && $specialLeaveTimeOffId && $academicYearId) {
                $specialLeaveEmployeeIds = LeaveAllocation::whereIn('employee_id', $employeeIds)
                    ->where('timeoff_id', $specialLeaveTimeOffId)
                    ->where('academic_year_id', $academicYearId)
                    ->pluck('employee_id')
                    ->flip();
            }

            $holidayShiftId = null;

            foreach ($employees as $employee) {
                $override = $overrides->get($employee->id);

                // Existing rows without a holiday reference are HRD's manual overrides.
                if ($override && $override->holiday_id === null) {
                    continue;
                }

                $branchId = optional($employee->employment)->branch_id;
                $applicableHoliday = $holidays->first(function ($holiday) use ($branchId, $employee, $specialLeaveEmployeeIds) {
                    $appliesToBranch = $holiday->branch_id === null
                        || (int) $holiday->branch_id === (int) $branchId;

                    if (!$appliesToBranch) {
                        return false;
                    }

                    $isNewAcademicYearHoliday = $holiday->type === Holiday::TYPE_SCHOOL
                        && $holiday->category === Holiday::CATEGORY_NEW_ACADEMIC_YEAR;

                    return !$isNewAcademicYearHoliday
                        || !$specialLeaveEmployeeIds->has($employee->id);
                });

                if (!$applicableHoliday) {
                    if ($override && $override->holiday_id !== null) {
                        $override->delete();
                    }
                    continue;
                }

                if ($holidayShiftId === null) {
                    $holidayShiftId = Shift::where('holiday', true)->value('id');
                    if ($holidayShiftId === null) {
                        throw new \RuntimeException('No shift marked as a holiday/day off is configured.');
                    }
                }

                if (!$override) {
                    $override = new EmployeeShiftOverride();
                    $override->employee_id = $employee->id;
                    $override->date = $date;
                    $override->created_by = auth()->id();
                }

                $override->shift_id = $holidayShiftId;
                $override->holiday_id = $applicableHoliday->id;
                $override->updated_by = auth()->id();
                $override->save();
            }
        }
    }

    function put($holiday, $request)
    {
        return DB::transaction(function () use ($holiday, $request) {
            $type = $holiday->type;
            $branchId = $request->input('branch_id') ?: null;
            $category = $type === Holiday::TYPE_SCHOOL ? $request->input('category') : null;
            $date = $request->input('start_date');
            $name = $request->input('name');

            $duplicateQuery = Holiday::whereDate('date', $date)
                ->where('type', $type)
                ->where('id', '<>', $holiday->id);

            if ($type === Holiday::TYPE_SCHOOL) {
                $duplicateQuery->where('category', $category);
            }

            if ($branchId === null) {
                $duplicateQuery->whereNull('branch_id');
            } else {
                $duplicateQuery->where('branch_id', $branchId);
            }

            if ($duplicateQuery->exists()) {
                throw ValidationException::withMessages([
                    'start_date' => 'A holiday already exists for this date, category, and branch.',
                ]);
            }

            $previousDate = $holiday->date->toDateString();
            $holiday->fill([
                'category' => $category,
                'description' => $request->input('description'),
                'branch_id' => $branchId,
                'date' => $date,
                'name' => $name,
            ])->save();

            $this->reconcileDates([$previousDate, $date]);

            return $holiday->load('branch');
        });
    }
    function delete($holiday)
    {
        return DB::transaction(function () use ($holiday) {
            if (!in_array($holiday->type, [Holiday::TYPE_COMPANY, Holiday::TYPE_SCHOOL], true)) {
                return;
            }

            $date = $holiday->date->toDateString();
            $holiday->delete();
            $this->reconcileDates([$date]);
        });
    }
}
