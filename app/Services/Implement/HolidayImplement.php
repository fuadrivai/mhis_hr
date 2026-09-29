<?php

namespace App\Services\Implement;

use App\Models\Holiday;
use App\Services\HolidayService;
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

            return collect($dates)->map(function ($date) use ($request, $type, $branchId, $category, $name) {
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
        });
    }
    function put($holiday, $request)
    {
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

        $holiday->fill([
            'category' => $category,
            'description' => $request->input('description'),
            'branch_id' => $branchId,
            'date' => $date,
            'name' => $name,
        ])->save();

        return $holiday->load('branch');
    }
    function delete($holiday)
    {
        if (in_array($holiday->type, [Holiday::TYPE_COMPANY, Holiday::TYPE_SCHOOL], true)) {
            $holiday->delete();
        }
    }
}
