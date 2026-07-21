<?php

namespace App\Http\Controllers;

use App\Models\LessonPlanTarget;
use App\Models\SubjectCategoryMonitor;
use App\Models\LessonPlanSubmission;
use Illuminate\Http\Request;

class LessonPlanMonitoringController extends Controller
{
    public function index()
    {
        $title = 'Monitoring Lesson Plan';
        $employeeId = auth()->user()->employee->id ?? null;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        // Check if the user is a monitor for any subject categories
        $monitorRoles = SubjectCategoryMonitor::where('employee_id', $employeeId)->get();

        if ($monitorRoles->isEmpty()) {
            return redirect()->back()->with('error', 'You do not have access to monitor lesson plans.');
        }

        // Fetch all lesson plan targets
        $targets = LessonPlanTarget::with('months')->orderBy('deadline_date', 'desc')->get();

        return view('employee.lesson_plan.monitoring.index', compact('title', 'targets', 'monitorRoles'));
    }

    public function showTarget($id)
    {
        $title = 'Monitoring Lesson Plan Target Details';
        $employeeId = auth()->user()->employee->id ?? null;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        $target = LessonPlanTarget::with('months')->findOrFail($id);

        $monitorRoles = SubjectCategoryMonitor::where('employee_id', $employeeId)->get();
        if ($monitorRoles->isEmpty()) {
            return redirect()->back()->with('error', 'You do not have access to monitor lesson plans.');
        }

        $monitoredCategoryIds = $monitorRoles->pluck('subject_category_id')->toArray();

        // Get Employee Subjects that fall under these categories
        $employeeSubjects = \App\Models\EmployeeSubject::with(['employee.user', 'subject.subjectCategory', 'schoolClass'])
            ->whereHas('subject', function($q) use ($monitoredCategoryIds) {
                $q->whereIn('subject_category_id', $monitoredCategoryIds);
            })
            ->get();

        // Get all submissions for this target and these employee subjects
        $submissions = LessonPlanSubmission::whereHas('lessonPlanTargetMonth', function($q) use ($id) {
                $q->where('lesson_plan_target_id', $id);
            })
            ->whereIn('employee_subject_id', $employeeSubjects->pluck('id'))
            ->get();

        $expectedSubmissionsPerES = $target->months->sum(function($month) {
            return $month->has_5_weeks ? 5 : 4;
        });

        $groupedData = [];

        foreach ($employeeSubjects as $es) {
            $catName = $es->subject->subjectCategory->name ?? 'Unknown Category';
            $subName = $es->subject->name ?? 'Unknown Subject';
            $subjectId = $es->subject_id;

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

            $approvedCount = $submissions->where('employee_subject_id', $es->id)->where('status', 'approved')->count();
            $submittedCount = $submissions->where('employee_subject_id', $es->id)->where('status', 'submitted')->count();
            $revisionCount = $submissions->where('employee_subject_id', $es->id)->where('status', 'need_revision')->count();
            
            $groupedData[$subjectId]['total_approved'] += $approvedCount;
            $groupedData[$subjectId]['total_submitted'] += $submittedCount;
            $groupedData[$subjectId]['total_revision'] += $revisionCount;
            $groupedData[$subjectId]['total_expected'] += $expectedSubmissionsPerES;
        }

        // Calculate overall progress for each subject
        foreach ($groupedData as &$data) {
            $data['progress_approved'] = $data['total_expected'] > 0 ? round(($data['total_approved'] / $data['total_expected']) * 100) : 0;
            $data['progress_submitted'] = $data['total_expected'] > 0 ? round(($data['total_submitted'] / $data['total_expected']) * 100) : 0;
            $data['progress_revision'] = $data['total_expected'] > 0 ? round(($data['total_revision'] / $data['total_expected']) * 100) : 0;
            $data['progress'] = $data['progress_approved']; // For backward compatibility if needed
        }

        return view('employee.lesson_plan.monitoring.show', compact('title', 'target', 'groupedData'));
    }

    public function showSubject($id, $subject_id)
    {
        $title = 'Monitoring Lesson Plan Subject Details';
        $target = LessonPlanTarget::with('months')->findOrFail($id);
        
        $user = auth()->user();
        $employeeId = $user->employee->id ?? 0;

        $monitorRoles = \App\Models\SubjectCategoryMonitor::where('employee_id', $employeeId)->get();
        if ($monitorRoles->isEmpty()) {
            return redirect()->route('employee.lesson-plan.monitoring.index')->with('error', 'You are not assigned as a monitor.');
        }

        $monitoredCategoryIds = $monitorRoles->pluck('subject_category_id')->toArray();
        $subject = \App\Models\Subject::with('subjectCategory')->findOrFail($subject_id);

        if (!in_array($subject->subject_category_id, $monitoredCategoryIds)) {
            return redirect()->route('employee.lesson-plan.monitoring.show', $id)->with('error', 'You do not have permission to monitor this subject.');
        }

        $employeeSubjects = \App\Models\EmployeeSubject::with(['employee.user', 'schoolClass'])
            ->where('subject_id', $subject_id)
            ->get();

        $submissions = LessonPlanSubmission::with(['approvals.approverEmployee.user', 'lessonPlanTargetMonth'])
            ->whereHas('lessonPlanTargetMonth', function($q) use ($id) {
                $q->where('lesson_plan_target_id', $id);
            })
            ->whereIn('employee_subject_id', $employeeSubjects->pluck('id'))
            ->get();

        $expectedSubmissionsPerES = $target->months->sum(function($month) {
            return $month->has_5_weeks ? 5 : 4;
        });

        $details = [];
        foreach ($employeeSubjects as $es) {
            $esSubmissions = $submissions->where('employee_subject_id', $es->id);
            
            $approvedCount = $esSubmissions->where('status', 'approved')->count();
            $submittedCount = $esSubmissions->where('status', 'submitted')->count();
            $revisionCount = $esSubmissions->where('status', 'need_revision')->count();
            
            $details[] = [
                'employee_name' => $es->employee->user->name ?? 'Unknown User',
                'class_name' => $es->schoolClass->name ?? '',
                'approved_count' => $approvedCount,
                'submitted_count' => $submittedCount,
                'revision_count' => $revisionCount,
                'expected_count' => $expectedSubmissionsPerES,
                'progress_approved' => $expectedSubmissionsPerES > 0 ? round(($approvedCount / $expectedSubmissionsPerES) * 100) : 0,
                'progress_submitted' => $expectedSubmissionsPerES > 0 ? round(($submittedCount / $expectedSubmissionsPerES) * 100) : 0,
                'progress_revision' => $expectedSubmissionsPerES > 0 ? round(($revisionCount / $expectedSubmissionsPerES) * 100) : 0,
                'progress' => $expectedSubmissionsPerES > 0 ? round(($approvedCount / $expectedSubmissionsPerES) * 100) : 0,
                'submissions' => $esSubmissions
            ];
        }

        return view('employee.lesson_plan.monitoring.subject', compact('title', 'target', 'subject', 'details'));
    }
}
