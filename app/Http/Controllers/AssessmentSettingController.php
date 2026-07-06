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
        $approvers = \App\Models\AssessmentApprover::with(['subjectCategory', 'employee'])->get();
        $employeeSubjects = \App\Models\AssessmentAssignment::with(['employee', 'subject', 'schoolClass'])->get();
        $employees = \App\Models\Employee::with('user')->get();

        return view('settings.assessment.index', compact(
            'title', 'classes', 'categories', 'subjects', 'approvers', 'employeeSubjects', 'employees'
        ));
    }

    // --- Approver ---
    public function storeApprover(Request $request)
    {
        $request->validate([
            'subject_category_id' => 'required|exists:subject_categories,id',
            'employee_id' => 'required|exists:employees,id',
            'level' => 'required|integer|min:1'
        ]);
        \App\Models\AssessmentApprover::create($request->all());
        return redirect()->back()->with('success', 'Approver added successfully');
    }
    public function destroyApprover($id)
    {
        \App\Models\AssessmentApprover::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Approver deleted');
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
