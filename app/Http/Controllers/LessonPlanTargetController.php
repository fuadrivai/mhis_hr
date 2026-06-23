<?php

namespace App\Http\Controllers;

use App\Models\LessonPlanTarget;
use App\Models\LessonPlanTargetMonth;
use Illuminate\Http\Request;

class LessonPlanTargetController extends Controller
{
    public function index()
    {
        $title = 'Lesson Plan Targets';
        $targets = LessonPlanTarget::with('months')->orderBy('deadline_date', 'desc')->get();
        return view('settings.lesson_plan.target', compact('title', 'targets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'deadline_date' => 'required|date',
            'description' => 'nullable|string',
            'months' => 'required|array|min:1',
            'years' => 'required|array|min:1'
        ]);

        $target = LessonPlanTarget::create([
            'deadline_date' => $request->deadline_date,
            'description' => $request->description,
            'created_by' => auth()->id()
        ]);

        foreach ($request->months as $key => $month) {
            LessonPlanTargetMonth::create([
                'lesson_plan_target_id' => $target->id,
                'month' => $month,
                'year' => $request->years[$key]
            ]);
        }

        return redirect()->back()->with('success', 'Target created successfully');
    }

    public function destroy($id)
    {
        LessonPlanTarget::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Target deleted');
    }
}
