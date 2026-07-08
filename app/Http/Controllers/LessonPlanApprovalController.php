<?php

namespace App\Http\Controllers;

use App\Models\LessonPlanSubmission;
use App\Models\LessonPlanApproval;
use App\Models\SubjectCategoryApprover;
use Illuminate\Http\Request;

class LessonPlanApprovalController extends Controller
{
    public function index()
    {
        $title = 'Lesson Plan Approvals';
        $employeeId = auth()->user()->employee->id ?? null;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        // Find all submissions where this employee is the approver for the current level
        // First, get the subject categories where this employee is an approver, and their level
        $approverRoles = SubjectCategoryApprover::where('employee_id', $employeeId)->get();

        $pendingSubmissions = collect();

        foreach ($approverRoles as $role) {
            $submissions = LessonPlanSubmission::with(['employeeSubject.employee.user', 'employeeSubject.subject', 'employeeSubject.schoolClass', 'lessonPlanTargetMonth.lessonPlanTarget'])
                ->where('status', 'submitted')
                ->where('current_approval_level', $role->level)
                ->whereHas('employeeSubject.subject', function($q) use ($role) {
                    $q->where('id', $role->subject_id);
                })->get();
            
            $pendingSubmissions = $pendingSubmissions->merge($submissions);
        }

        return view('employee.lesson_plan.approvals', compact('title', 'pendingSubmissions'));
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,need_revision',
            'comments' => 'nullable|string'
        ]);

        $submission = LessonPlanSubmission::findOrFail($id);
        $employeeId = auth()->user()->employee->id;

        // Record the approval history
        LessonPlanApproval::create([
            'lesson_plan_submission_id' => $submission->id,
            'approver_employee_id' => $employeeId,
            'status' => $request->status,
            'comments' => $request->comments
        ]);

        if ($request->status === 'need_revision') {
            $submission->status = 'need_revision';
            $submission->save();
            
            if ($submission->employeeSubject->employee->user) {
                $submission->employeeSubject->employee->user->notify(new \App\Notifications\LessonPlanNeedRevision($submission, $request->comments));
            }

            return redirect()->back()->with('success', 'Sent back for revision.');
        } else {
            // Find if there is a next level
            $subjectId = $submission->employeeSubject->subject_id;
            $currentLevel = $submission->current_approval_level;

            $nextLevelApprover = SubjectCategoryApprover::where('subject_id', $subjectId)
                                    ->where('level', '>', $currentLevel)
                                    ->orderBy('level', 'asc')
                                    ->first();

            if ($nextLevelApprover) {
                $submission->current_approval_level = $nextLevelApprover->level;
                $submission->save();
                
                if ($nextLevelApprover->employee && $nextLevelApprover->employee->user) {
                    $nextLevelApprover->employee->user->notify(new \App\Notifications\LessonPlanSubmitted($submission));
                }

                return redirect()->back()->with('success', 'Approved. Passed to next level.');
            } else {
                $submission->status = 'approved';
                $submission->save();
                return redirect()->back()->with('success', 'Fully Approved.');
            }
        }
    }
}
