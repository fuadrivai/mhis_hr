<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    public function index()
    {
        $title = "Academic Years";
        $years = AcademicYear::orderBy('created_at', 'desc')->get();
        return view('settings.academic_year.index', compact('title', 'years'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'start_date' => $this->normalizeDateInput($request->start_date),
            'end_date' => $this->normalizeDateInput($request->end_date),
        ]);

        $request->validate([
            'name' => 'required|string|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        AcademicYear::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => false,
        ]);

        return redirect()->back()->with('success', 'Academic Year added successfully.');
    }

    public function update(Request $request, $id)
    {
        $year = AcademicYear::findOrFail($id);

        $request->merge([
            'start_date' => $this->normalizeDateInput($request->start_date),
            'end_date' => $this->normalizeDateInput($request->end_date),
        ]);

        $request->validate([
            'name' => 'required|string|unique:academic_years,name,' . $year->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $year->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->back()->with('success', 'Academic Year updated successfully.');
    }

    public function destroy($id)
    {
        $year = AcademicYear::findOrFail($id);
        $year->delete();
        return redirect()->back()->with('success', 'Academic Year deleted successfully.');
    }

    public function setActive($id)
    {
        DB::transaction(function () use ($id) {
            AcademicYear::query()->update(['is_active' => false]);
            $year = AcademicYear::findOrFail($id);
            $year->update(['is_active' => true]);
        });

        return redirect()->back()->with('success', 'Active Academic Year updated successfully.');
    }

    private function normalizeDateInput($value)
    {
        if (!$value) {
            return $value;
        }

        foreach (['Y-m-d', 'd F Y', 'd M Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value))->format('Y-m-d');
            } catch (\Exception $exception) {
            }
        }

        return $value;
    }
}
