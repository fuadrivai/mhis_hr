<?php

namespace App\Http\Controllers;

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
        $request->validate([
            'name' => 'required|string|unique:academic_years,name',
        ]);

        AcademicYear::create([
            'name' => $request->name,
            'is_active' => false,
        ]);

        return redirect()->back()->with('success', 'Academic Year added successfully.');
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
}
