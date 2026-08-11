<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssessmentSettingController extends Controller
{
    public function index()
    {
        $title = 'Assessment Settings';
        $classes = \App\Models\SchoolClass::all();
        $categories = \App\Models\SubjectCategory::all();
        $subjects = \App\Models\Subject::with('subjectCategory')->get();
        $approvers = \App\Models\AssessmentApprover::with(['subjectCategory', 'employee.user', 'schoolClasses', 'subjects'])->get();
        $monitors = \App\Models\AssessmentMonitor::with(['subjectCategory', 'employee'])->get();
        $employeeSubjects = \App\Models\AssessmentAssignment::with(['employee', 'subject', 'schoolClass'])->get();
        $employees = \App\Models\Employee::with('user')->get();

        return view('settings.assessment.index', compact(
            'title', 'classes', 'categories', 'subjects', 'approvers', 'monitors', 'employeeSubjects', 'employees'
        ));
    }

    // --- Approver ---
    public function storeApprover(Request $request)
    {
        $request->validate([
            'subject_category_id' => 'required|exists:subject_categories,id',
            'employee_id' => 'required|exists:employees,id',
            'level' => 'required|integer|min:1',
            'school_class_ids' => 'nullable|array',
            'school_class_ids.*' => 'exists:school_classes,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);
        $approver = \App\Models\AssessmentApprover::create($request->only('subject_category_id', 'employee_id', 'level'));
        if ($request->has('school_class_ids')) {
            $approver->schoolClasses()->sync($request->school_class_ids);
        }
        if ($request->has('subject_ids')) {
            $approver->subjects()->sync($request->subject_ids);
        }
        return redirect()->back()->with('success', 'Approver added successfully');
    }

    public function updateApprover(Request $request, $id)
    {
        $request->validate([
            'subject_category_id' => 'required|exists:subject_categories,id',
            'employee_id' => 'required|exists:employees,id',
            'level' => 'required|integer|min:1',
            'school_class_ids' => 'nullable|array',
            'school_class_ids.*' => 'exists:school_classes,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);

        $approver = \App\Models\AssessmentApprover::findOrFail($id);
        $approver->update($request->only('subject_category_id', 'employee_id', 'level'));
        
        if ($request->has('school_class_ids')) {
            $approver->schoolClasses()->sync($request->school_class_ids);
        } else {
            $approver->schoolClasses()->sync([]);
        }

        if ($request->has('subject_ids')) {
            $approver->subjects()->sync($request->subject_ids);
        } else {
            $approver->subjects()->sync([]);
        }

        return redirect()->back()->with('success', 'Approver updated successfully');
    }

    public function destroyApprover($id)
    {
        \App\Models\AssessmentApprover::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Approver deleted');
    }

    // --- Monitor ---
    public function storeMonitor(Request $request)
    {
        $request->validate([
            'subject_category_id' => 'required|exists:subject_categories,id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        \App\Models\AssessmentMonitor::create([
            'subject_category_id' => $request->subject_category_id,
            'employee_id' => $request->employee_id,
        ]);

        return redirect()->back()->with('success', 'Monitor added successfully!');
    }

    public function destroyMonitor($id)
    {
        $monitor = \App\Models\AssessmentMonitor::findOrFail($id);
        $monitor->delete();
        return redirect()->back()->with('success', 'Monitor removed successfully!');
    }

    // --- Employee Assignment ---
    public function storeAssignment(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'subject_id' => 'required|exists:subjects,id',
            'school_class_id' => 'required|exists:school_classes,id'
        ]);
        \App\Models\AssessmentAssignment::create($request->all());
        return redirect()->back()->with('success', 'Assignment added successfully');
    }
    public function destroyAssignment($id)
    {
        \App\Models\AssessmentAssignment::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Assignment deleted');
    }
}
