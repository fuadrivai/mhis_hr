<?php

namespace App\Http\Controllers;

use App\Models\AssessmentTarget;
use App\Models\AssessmentMonitor;
use App\Models\AssessmentSubmission;
use Illuminate\Http\Request;

class AssessmentMonitoringController extends Controller
{
    public function index()
    {
        $title = 'Monitoring Assessment';
        $employeeId = auth()->user()->employee->id ?? null;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        // Check if the user is a monitor for any subject categories
        $monitorRoles = AssessmentMonitor::where('employee_id', $employeeId)->get();

        if ($monitorRoles->isEmpty()) {
            return redirect()->back()->with('error', 'You do not have access to monitor assessments.');
        }

        // Fetch all assessment targets
        $targets = AssessmentTarget::orderBy('deadline_date', 'desc')->get();

        return view('employee.assessment.monitoring.index', compact('title', 'targets', 'monitorRoles'));
    }

    public function showTarget($id)
    {
        $title = 'Monitoring Assessment Target Details';
        $employeeId = auth()->user()->employee->id ?? null;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        $target = AssessmentTarget::findOrFail($id);

        $monitorRoles = AssessmentMonitor::where('employee_id', $employeeId)->get();
        if ($monitorRoles->isEmpty()) {
            return redirect()->back()->with('error', 'You do not have access to monitor assessments.');
        }

        $monitoredCategoryIds = $monitorRoles->pluck('subject_category_id')->toArray();

        // Get Assessment Assignments that fall under these categories
        $assignments = \App\Models\AssessmentAssignment::with(['employee.user', 'subject.subjectCategory', 'schoolClass'])
            ->whereHas('subject', function($q) use ($monitoredCategoryIds) {
                $q->whereIn('subject_category_id', $monitoredCategoryIds);
            })
            ->get();

        // Get all submissions for this target and these assignments
        $submissions = AssessmentSubmission::where('assessment_target_id', $id)
            ->whereIn('assessment_assignment_id', $assignments->pluck('id'))
            ->get();

        $groupedData = [];

        foreach ($assignments as $assignment) {
            $catName = $assignment->subject->subjectCategory->name ?? 'Unknown Category';
            $subName = $assignment->subject->name ?? 'Unknown Subject';
            $subjectId = $assignment->subject_id;

            if (!isset($groupedData[$subjectId])) {
                $groupedData[$subjectId] = [
                    'category_name' => $catName,
                    'subject_name' => $subName,
                    'total_approved' => 0,
                    'total_submitted' => 0,
                    'total_revision' => 0,
                    'total_expected' => 0,
                    'details' => []
                ];
            }

            $assignmentSubmission = $submissions->where('assessment_assignment_id', $assignment->id)->first();
            
            if ($assignmentSubmission) {
                if ($assignmentSubmission->status == 'approved') {
                    $groupedData[$subjectId]['total_approved'] += 1;
                } elseif ($assignmentSubmission->status == 'submitted') {
                    $groupedData[$subjectId]['total_submitted'] += 1;
                } elseif ($assignmentSubmission->status == 'need_revision') {
                    $groupedData[$subjectId]['total_revision'] += 1;
                }
            }
            
            $groupedData[$subjectId]['total_expected'] += 1;
        }

        // Calculate overall progress for each subject
        foreach ($groupedData as &$data) {
            $data['progress_approved'] = $data['total_expected'] > 0 ? round(($data['total_approved'] / $data['total_expected']) * 100) : 0;
            $data['progress_submitted'] = $data['total_expected'] > 0 ? round(($data['total_submitted'] / $data['total_expected']) * 100) : 0;
            $data['progress_revision'] = $data['total_expected'] > 0 ? round(($data['total_revision'] / $data['total_expected']) * 100) : 0;
            $data['progress'] = $data['progress_approved'];
        }

        return view('employee.assessment.monitoring.show', compact('title', 'target', 'groupedData'));
    }

    public function showSubject($id, $subject_id)
    {
        $title = 'Monitoring Assessment Subject Details';
        $target = AssessmentTarget::findOrFail($id);
        
        $user = auth()->user();
        $employeeId = $user->employee->id ?? 0;

        $monitorRoles = AssessmentMonitor::where('employee_id', $employeeId)->get();
        if ($monitorRoles->isEmpty()) {
            return redirect()->route('employee.assessment.monitoring.index')->with('error', 'You are not assigned as a monitor.');
        }

        $monitoredCategoryIds = $monitorRoles->pluck('subject_category_id')->toArray();
        $subject = \App\Models\Subject::with('subjectCategory')->findOrFail($subject_id);

        if (!in_array($subject->subject_category_id, $monitoredCategoryIds)) {
            return redirect()->route('employee.assessment.monitoring.show', $id)->with('error', 'You do not have permission to monitor this subject.');
        }

        $assignments = \App\Models\AssessmentAssignment::with(['employee.user', 'schoolClass'])
            ->where('subject_id', $subject_id)
            ->get();

        $submissions = AssessmentSubmission::with(['approvals.approverEmployee.user'])
            ->where('assessment_target_id', $id)
            ->whereIn('assessment_assignment_id', $assignments->pluck('id'))
            ->get();

        $details = [];
        foreach ($assignments as $assignment) {
            $assignmentSubmission = $submissions->where('assessment_assignment_id', $assignment->id)->first();
            
            $approvedCount = 0;
            $submittedCount = 0;
            $revisionCount = 0;
            
            if ($assignmentSubmission) {
                if ($assignmentSubmission->status == 'approved') {
                    $approvedCount = 1;
                } elseif ($assignmentSubmission->status == 'submitted') {
                    $submittedCount = 1;
                } elseif ($assignmentSubmission->status == 'need_revision') {
                    $revisionCount = 1;
                }
            }
            
            $expectedCount = 1;
            
            $details[] = [
                'employee_name' => $assignment->employee->user->name ?? 'Unknown User',
                'class_name' => $assignment->schoolClass->name ?? '',
                'approved_count' => $approvedCount,
                'submitted_count' => $submittedCount,
                'revision_count' => $revisionCount,
                'expected_count' => $expectedCount,
                'progress_approved' => $expectedCount > 0 ? round(($approvedCount / $expectedCount) * 100) : 0,
                'progress_submitted' => $expectedCount > 0 ? round(($submittedCount / $expectedCount) * 100) : 0,
                'progress_revision' => $expectedCount > 0 ? round(($revisionCount / $expectedCount) * 100) : 0,
                'progress' => $expectedCount > 0 ? round(($approvedCount / $expectedCount) * 100) : 0,
                'submission' => $assignmentSubmission
            ];
        }

        return view('employee.assessment.monitoring.subject', compact('title', 'target', 'subject', 'details'));
    }
}
