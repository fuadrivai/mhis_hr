<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssessmentTargetController extends Controller
{
    public function index()
    {
        $title = 'Assessment Targets';
        $targets = \App\Models\AssessmentTarget::orderBy('deadline_date', 'desc')->get();
        return view('settings.assessment.target', compact('title', 'targets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'deadline_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        \App\Models\AssessmentTarget::create([
            'deadline_date' => $request->deadline_date,
            'description' => $request->description,
            'created_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Target created successfully');
    }

    public function destroy($id)
    {
        \App\Models\AssessmentTarget::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Target deleted');
    }
}
