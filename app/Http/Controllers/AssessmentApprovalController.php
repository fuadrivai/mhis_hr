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
            $classIds = $role->schoolClasses->pluck('id')->toArray();
            $subjectIds = $role->subjects->pluck('id')->toArray();
            
            $query = \App\Models\AssessmentSubmission::with(['assignment.employee.user', 'assignment.subject', 'assignment.schoolClass', 'target'])
                ->where('status', 'submitted')
                ->where('current_approval_level', $role->level)
                ->whereHas('assignment.subject', function($q) use ($role) {
                    $q->where('subject_category_id', $role->subject_category_id);
                });
                
            if (!empty($classIds)) {
                $query->whereHas('assignment', function($q) use ($classIds) {
                    $q->whereIn('school_class_id', $classIds);
                });
            }

            if (!empty($subjectIds)) {
                $query->whereHas('assignment', function($q) use ($subjectIds) {
                    $q->whereIn('subject_id', $subjectIds);
                });
            }

            $submissions = $query->get();
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

            $schoolClassId = $submission->assignment->school_class_id;
            $subjectId = $submission->assignment->subject_id;

            $nextLevelApprover = \App\Models\AssessmentApprover::where('subject_category_id', $subjectCategoryId)
                                    ->where('level', '>', $currentLevel)
                                    ->where(function($q) use ($schoolClassId) {
                                        $q->whereHas('schoolClasses', function($sq) use ($schoolClassId) {
                                            $sq->where('school_classes.id', $schoolClassId);
                                        })->orWhereDoesntHave('schoolClasses');
                                    })
                                    ->where(function($q) use ($subjectId) {
                                        $q->whereHas('subjects', function($sq) use ($subjectId) {
                                            $sq->where('subjects.id', $subjectId);
                                        })->orWhereDoesntHave('subjects');
                                    })
                                    ->withCount(['schoolClasses', 'subjects'])
                                    ->orderBy('level', 'asc')
                                    ->orderByDesc('subjects_count')
                                    ->orderByDesc('school_classes_count')
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
