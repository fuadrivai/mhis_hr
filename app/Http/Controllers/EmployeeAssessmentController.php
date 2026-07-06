<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeeAssessmentController extends Controller
{
    public function index()
    {
        $title = 'My Assessments';
        $employeeId = auth()->user()->employee->id ?? null;
        
        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        $assignments = \App\Models\AssessmentAssignment::where('employee_id', $employeeId)->with(['subject', 'schoolClass'])->get();
        $targets = \App\Models\AssessmentTarget::orderBy('deadline_date', 'desc')->get();

        return view('employee.assessment.index', compact('title', 'assignments', 'targets'));
    }

    public function showTarget($targetId, $assignmentId)
    {
        $title = 'Submit Assessment';
        $target = \App\Models\AssessmentTarget::findOrFail($targetId);
        $assignment = \App\Models\AssessmentAssignment::with(['subject', 'schoolClass'])->findOrFail($assignmentId);
        
        if ($assignment->employee_id !== auth()->user()->employee->id) {
            abort(403);
        }

        $submission = \App\Models\AssessmentSubmission::where('assessment_assignment_id', $assignmentId)
                            ->where('assessment_target_id', $targetId)
                            ->first();

        return view('employee.assessment.submit', compact('title', 'target', 'assignment', 'submission'));
    }

    public function submit(Request $request, $targetId, $assignmentId)
    {
        $request->validate([
            'title' => 'required|string',
            'file_link' => 'required|url',
        ]);

        $assignment = \App\Models\AssessmentAssignment::findOrFail($assignmentId);
        if ($assignment->employee_id !== auth()->user()->employee->id) {
            abort(403);
        }

        $submission = \App\Models\AssessmentSubmission::updateOrCreate(
            [
                'assessment_assignment_id' => $assignmentId,
                'assessment_target_id' => $targetId
            ],
            [
                'title' => $request->title,
                'file_link' => $request->file_link,
                'status' => 'submitted',
                'current_approval_level' => 1
            ]
        );

        $approver = \App\Models\AssessmentApprover::where('subject_category_id', $assignment->subject->subject_category_id)
                        ->where('level', 1)->first();
        if ($approver && $approver->employee && $approver->employee->user) {
            $approver->employee->user->notify(new \App\Notifications\AssessmentSubmitted($submission));
        }

        return redirect()->back()->with('success', "Assessment submitted successfully!");
    }
}
