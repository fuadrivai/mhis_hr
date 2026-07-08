<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\SubjectCategory;
use App\Models\Subject;
use App\Models\SubjectCategoryApprover;
use App\Models\SubjectCategoryMonitor;
use App\Models\EmployeeSubject;
use App\Models\Employee;
use Illuminate\Http\Request;

class LessonPlanSettingController extends Controller
{
    public function index()
    {
        $title = 'Lesson Plan Settings';
        $classes = SchoolClass::all();
        $categories = SubjectCategory::all();
        $subjects = Subject::with('subjectCategory')->get();
        $approvers = SubjectCategoryApprover::with(['subject', 'employee', 'schoolClass'])->get();
        $monitors = SubjectCategoryMonitor::with(['subjectCategory', 'employee'])->get();
        $employeeSubjects = EmployeeSubject::with(['employee', 'subject', 'schoolClass'])->get();
        $employees = Employee::with('user')->get();

        return view('settings.lesson_plan.index', compact(
            'title', 'classes', 'categories', 'subjects', 'approvers', 'monitors', 'employeeSubjects', 'employees'
        ));
    }

    // --- School Class ---
    public function storeClass(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        SchoolClass::create($request->all());
        return redirect()->back()->with('success', 'Class created successfully');
    }
    public function destroyClass($id)
    {
        SchoolClass::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Class deleted');
    }

    // --- Subject Category ---
    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        SubjectCategory::create($request->all());
        return redirect()->back()->with('success', 'Category created successfully');
    }
    public function destroyCategory($id)
    {
        SubjectCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Category deleted');
    }

    // --- Subject ---
    public function storeSubject(Request $request)
    {
        $request->validate(['name' => 'required|string', 'subject_category_id' => 'required|exists:subject_categories,id']);
        Subject::create($request->all());
        return redirect()->back()->with('success', 'Subject created successfully');
    }
    public function destroySubject($id)
    {
        Subject::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Subject deleted');
    }

    // --- Approver ---
    public function storeApprover(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'employee_id' => 'required|exists:employees,id',
            'level' => 'required|integer|min:1'
        ]);
        SubjectCategoryApprover::create($request->all());
        return redirect()->back()->with('success', 'Approver added successfully');
    }
    public function destroyApprover($id)
    {
        SubjectCategoryApprover::findOrFail($id)->delete();
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
        EmployeeSubject::create($request->all());
        return redirect()->back()->with('success', 'Assignment added successfully');
    }
    public function destroyAssignment($id)
    {
        EmployeeSubject::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Assignment deleted');
    }

    // --- Monitor ---
    public function storeMonitor(Request $request)
    {
        $request->validate([
            'subject_category_id' => 'required|exists:subject_categories,id',
            'employee_id' => 'required|exists:employees,id'
        ]);
        
        // Prevent duplicate assignment
        $exists = SubjectCategoryMonitor::where('subject_category_id', $request->subject_category_id)
            ->where('employee_id', $request->employee_id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()->with('error', 'Monitor already assigned to this category.');
        }

        SubjectCategoryMonitor::create($request->all());
        return redirect()->back()->with('success', 'Monitor added successfully');
    }

    public function destroyMonitor($id)
    {
        SubjectCategoryMonitor::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Monitor deleted');
    }
}
