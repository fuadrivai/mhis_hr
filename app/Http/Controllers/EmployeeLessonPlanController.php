<?php

namespace App\Http\Controllers;

use App\Models\LessonPlanTarget;
use App\Models\LessonPlanTargetMonth;
use App\Models\LessonPlanSubmission;
use App\Models\EmployeeSubject;
use Illuminate\Http\Request;

class EmployeeLessonPlanController extends Controller
{
    public function index()
    {
        $title = 'My Lesson Plans';
        $employeeId = auth()->user()->employee->id ?? null;
        
        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        // Get subjects assigned to this employee
        $assignments = EmployeeSubject::where('employee_id', $employeeId)->with(['subject', 'schoolClass'])->get();
        
        // Get all active targets
        $targets = LessonPlanTarget::with('months')->orderBy('deadline_date', 'desc')->get();

        return view('employee.lesson_plan.index', compact('title', 'assignments', 'targets'));
    }

    public function showTarget($targetId, $assignmentId)
    {
        $title = 'Submit Lesson Plan';
        $target = LessonPlanTarget::with('months')->findOrFail($targetId);
        $assignment = EmployeeSubject::with(['subject', 'schoolClass'])->findOrFail($assignmentId);
        
        // Ensure the assignment belongs to the logged in employee
        if ($assignment->employee_id !== auth()->user()->employee->id) {
            abort(403);
        }

        // Get existing submissions for this assignment and target's months
        $monthIds = $target->months->pluck('id');
        $submissions = LessonPlanSubmission::where('employee_subject_id', $assignmentId)
                            ->whereIn('lesson_plan_target_month_id', $monthIds)
                            ->get()
                            ->groupBy('lesson_plan_target_month_id');

        return view('employee.lesson_plan.submit', compact('title', 'target', 'assignment', 'submissions'));
    }

    public function submit(Request $request, $monthId, $assignmentId, $week)
    {
        $request->validate([
            'title' => 'required|string',
            'file_link' => 'required|url',
        ]);

        $assignment = EmployeeSubject::findOrFail($assignmentId);
        if ($assignment->employee_id !== auth()->user()->employee->id) {
            abort(403);
        }

        $submission = LessonPlanSubmission::updateOrCreate(
            [
                'employee_subject_id' => $assignmentId,
                'lesson_plan_target_month_id' => $monthId,
                'week_number' => $week
            ],
            [
                'title' => $request->title,
                'file_link' => $request->file_link,
                'status' => 'submitted',
                'current_approval_level' => 1
            ]
        );

        // Send email notification to the level 1 approver
        $approver = \App\Models\SubjectCategoryApprover::where('subject_id', $assignment->subject_id)
                        ->where('level', 1)->first();
        if ($approver && $approver->employee && $approver->employee->user) {
            $approver->employee->user->notify(new \App\Notifications\LessonPlanSubmitted($submission));
        }

        return redirect()->back()->with('success', "Week $week submitted successfully!");
    }
}
