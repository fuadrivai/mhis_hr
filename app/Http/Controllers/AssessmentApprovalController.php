<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssessmentApprovalController extends Controller
{
    public function index()
    {
        $title = 'Assessment Approvals';
        $employeeId = auth()->user()->employee->id ?? null;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        $approverRoles = \App\Models\AssessmentApprover::where('employee_id', $employeeId)->get();

        $pendingSubmissions = collect();

        foreach ($approverRoles as $role) {
            $submissions = \App\Models\AssessmentSubmission::with(['assignment.employee.user', 'assignment.subject', 'assignment.schoolClass', 'target'])
                ->where('status', 'submitted')
                ->where('current_approval_level', $role->level)
                ->whereHas('assignment.subject', function($q) use ($role) {
                    $q->where('subject_category_id', $role->subject_category_id);
                })->get();
            
            $pendingSubmissions = $pendingSubmissions->merge($submissions);
        }

        return view('employee.assessment.approvals', compact('title', 'pendingSubmissions'));
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,need_revision',
            'notes' => 'nullable|string'
        ]);

        $submission = \App\Models\AssessmentSubmission::findOrFail($id);
        $employeeId = auth()->user()->employee->id;

        \App\Models\AssessmentApproval::create([
            'assessment_submission_id' => $submission->id,
            'approver_id' => $employeeId,
            'status' => $request->status,
            'notes' => $request->notes,
            'level' => $submission->current_approval_level
        ]);

        if ($request->status === 'need_revision') {
            $submission->status = 'need_revision';
            $submission->save();
            
            if ($submission->assignment->employee->user) {
                $submission->assignment->employee->user->notify(new \App\Notifications\AssessmentNeedRevision($submission, $request->notes));
            }

            return redirect()->back()->with('success', 'Sent back for revision.');
        } else {
            $subjectCategoryId = $submission->assignment->subject->subject_category_id;
            $currentLevel = $submission->current_approval_level;

            $nextLevelApprover = \App\Models\AssessmentApprover::where('subject_category_id', $subjectCategoryId)
                                    ->where('level', '>', $currentLevel)
                                    ->orderBy('level', 'asc')
                                    ->first();

            if ($nextLevelApprover) {
                $submission->current_approval_level = $nextLevelApprover->level;
                $submission->save();
                
                if ($nextLevelApprover->employee && $nextLevelApprover->employee->user) {
                    $nextLevelApprover->employee->user->notify(new \App\Notifications\AssessmentSubmitted($submission));
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
