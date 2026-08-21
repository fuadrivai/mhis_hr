<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeShiftOverride;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeShiftOverrideController extends Controller
{
    public function create(Request $request)
    {
        $selectedDate = $request->query('date', now()->toDateString());
        $selectedEmployees = old('employee_ids', array_filter(array_map('intval', (array) $request->query('employee_ids', []))));
        $employees = Employee::where('is_active', true)
            ->with(['personal', 'employment'])
            ->orderBy('id')
            ->get();

        return view('employee.scheduler.override', [
            'title' => 'Assign Shift Override',
            'shifts' => Shift::orderBy('name')->get(),
            'employees' => $employees,
            'branches' => $employees->pluck('employment.branch')->filter()->unique('id')->sortBy('name')->values(),
            'organizations' => $employees->pluck('employment.organization')->filter()->unique('id')->sortBy('name')->values(),
            'levels' => $employees->pluck('employment.job_level')->filter()->unique('id')->sortBy('name')->values(),
            'positions' => $employees->pluck('employment.job_position')->filter()->unique('id')->sortBy('name')->values(),
            'selectedDate' => Carbon::parse($selectedDate)->toDateString(),
            'selectedEmployees' => $selectedEmployees,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'date' => 'required|date_format:Y-m-d',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer|distinct|exists:employees,id',
        ]);

        DB::transaction(function () use ($validated) {
            $activeEmployeeIds = Employee::whereIn('id', $validated['employee_ids'])
                ->where('is_active', true)
                ->pluck('id');

            if ($activeEmployeeIds->count() !== count($validated['employee_ids'])) {
                throw ValidationException::withMessages([
                    'employee_ids' => 'Only active employees can receive a shift override.',
                ]);
            }

            foreach ($activeEmployeeIds as $employeeId) {
                $override = EmployeeShiftOverride::firstOrNew([
                    'employee_id' => $employeeId,
                    'date' => $validated['date'],
                ]);
                $override->shift_id = $validated['shift_id'];
                $override->updated_by = auth()->id();
                if (!$override->exists) {
                    $override->created_by = auth()->id();
                }
                $override->save();
            }
        });

        return redirect()->route('scheduler.index')->with('success', 'Shift override saved successfully.');
    }

    public function destroy(EmployeeShiftOverride $override)
    {
        $override->delete();

        return response()->json([
            'message' => 'Shift override removed successfully.',
        ]);
    }
}
