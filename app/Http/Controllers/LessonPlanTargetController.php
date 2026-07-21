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
                'year' => $request->years[$key],
                'has_5_weeks' => isset($request->has_5_weeks[$key]) ? true : false,
            ]);
        }

        return redirect()->back()->with('success', 'Target created successfully');
    }

    public function edit($id)
    {
        $title = 'Edit Lesson Plan Target';
        $target = LessonPlanTarget::with('months')->findOrFail($id);
        return view('settings.lesson_plan.target_edit', compact('title', 'target'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'deadline_date' => 'required|date',
            'description' => 'nullable|string',
            'months' => 'required|array|min:1',
            'years' => 'required|array|min:1'
        ]);

        $target = LessonPlanTarget::findOrFail($id);
        $target->update([
            'deadline_date' => $request->deadline_date,
            'description' => $request->description,
        ]);

        $existingMonths = $target->months;
        $requestedMonthIds = [];

        foreach ($request->months as $key => $month) {
            $year = $request->years[$key];
            $has_5_weeks = isset($request->has_5_weeks[$key]) ? true : false;
            
            // Check if month already exists for this target
            $targetMonth = $existingMonths->where('month', $month)->where('year', $year)->first();
            
            if ($targetMonth) {
                $targetMonth->update([
                    'has_5_weeks' => $has_5_weeks
                ]);
                $requestedMonthIds[] = $targetMonth->id;
            } else {
                $newMonth = LessonPlanTargetMonth::create([
                    'lesson_plan_target_id' => $target->id,
                    'month' => $month,
                    'year' => $year,
                    'has_5_weeks' => $has_5_weeks,
                ]);
                $requestedMonthIds[] = $newMonth->id;
            }
        }

        // Delete months that are no longer in the request
        $target->months()->whereNotIn('id', $requestedMonthIds)->delete();

        return redirect()->route('lesson-plan-target.index')->with('success', 'Target updated successfully');
    }

    public function destroy($id)
    {
        LessonPlanTarget::findOrFail($id)->delete();
        return redirect()->route('lesson-plan-target.index')->with('success', 'Target deleted');
    }
}
