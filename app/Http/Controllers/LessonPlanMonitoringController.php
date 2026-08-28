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

        return view('employee.lesson_plan.monitoring.show', compact('title', 'target', 'groupedData', 'monitorRoles'));
    }

    public function printTarget(Request $request, $id)
    {
        $title = 'Print Lesson Plan Target Details';
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
        $selectedCategories = $request->input('category_ids', []);
        
        // Filter monitored categories by selected categories if any are provided
        if (!empty($selectedCategories)) {
            $monitoredCategoryIds = array_intersect($monitoredCategoryIds, $selectedCategories);
        }

        if (empty($monitoredCategoryIds)) {
            return redirect()->back()->with('error', 'No categories selected to print.');
        }

        $employeeSubjects = \App\Models\EmployeeSubject::with(['employee.user', 'subject.subjectCategory', 'schoolClass'])
            ->whereHas('subject', function($q) use ($monitoredCategoryIds) {
                $q->whereIn('subject_category_id', $monitoredCategoryIds);
            })
            ->get();

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

        foreach ($groupedData as &$data) {
            $data['progress_approved'] = $data['total_expected'] > 0 ? round(($data['total_approved'] / $data['total_expected']) * 100) : 0;
            $data['progress_submitted'] = $data['total_expected'] > 0 ? round(($data['total_submitted'] / $data['total_expected']) * 100) : 0;
            $data['progress_revision'] = $data['total_expected'] > 0 ? round(($data['total_revision'] / $data['total_expected']) * 100) : 0;
            $data['progress'] = $data['progress_approved']; 
        }

        return view('employee.lesson_plan.monitoring.print', compact('title', 'target', 'groupedData'));
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

    public function notifyTarget(Request $request, $id)
    {
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
        $selectedCategories = $request->input('category_ids', []);
        
        if (!empty($selectedCategories)) {
            $monitoredCategoryIds = array_intersect($monitoredCategoryIds, $selectedCategories);
        }

        if (empty($monitoredCategoryIds)) {
            return redirect()->back()->with('error', 'No categories selected to notify.');
        }

        $whatsappNumbersInput = $request->input('whatsapp_numbers');
        if (empty($whatsappNumbersInput)) {
            return redirect()->back()->with('error', 'Please provide at least one WhatsApp number.');
        }

        $rawNumbers = array_filter(array_map('trim', explode(',', $whatsappNumbersInput)));
        $validNumbers = [];
        foreach ($rawNumbers as $num) {
            $num = preg_replace('/[^0-9]/', '', $num);
            if (substr($num, 0, 2) === '62') {
                $validNumbers[] = $num;
            } elseif (substr($num, 0, 1) === '0') {
                $validNumbers[] = '62' . substr($num, 1);
            }
        }

        if (empty($validNumbers)) {
            return redirect()->back()->with('error', 'No valid Indonesian WhatsApp numbers found (must start with 62 or 0).');
        }

        $employeeSubjects = \App\Models\EmployeeSubject::with(['employee.user', 'subject.subjectCategory', 'schoolClass'])
            ->whereHas('subject', function($q) use ($monitoredCategoryIds) {
                $q->whereIn('subject_category_id', $monitoredCategoryIds);
            })
            ->get();

        $submissions = LessonPlanSubmission::with(['lessonPlanTargetMonth'])
            ->whereHas('lessonPlanTargetMonth', function($q) use ($id) {
                $q->where('lesson_plan_target_id', $id);
            })
            ->whereIn('employee_subject_id', $employeeSubjects->pluck('id'))
            ->get();

        $expectedSubmissionsPerES = $target->months->sum(function($month) {
            return $month->has_5_weeks ? 5 : 4;
        });

        $teachersNotAchieved = [];
        $approversPending = [];

        $subjectIds = $employeeSubjects->pluck('subject_id')->unique();
        $classIds = $employeeSubjects->pluck('school_class_id')->unique()->filter();
        
        $approversQuery = \App\Models\SubjectCategoryApprover::with('employee.user')
            ->whereIn('subject_id', $subjectIds);
            
        if ($classIds->isNotEmpty()) {
            $approversQuery->where(function($q) use ($classIds) {
                $q->whereIn('school_class_id', $classIds)
                  ->orWhereNull('school_class_id');
            });
        }
        $approvers = $approversQuery->get();

        foreach ($employeeSubjects as $es) {
            $teacherName = $es->employee->user->name ?? 'Unknown Teacher';
            $subjectName = $es->subject->name ?? 'Unknown Subject';
            $className = $es->schoolClass->name ?? '';
            $catName = $es->subject->subjectCategory->name ?? 'Unknown Category';
            $subjectDisplay = $subjectName . ' (' . $className . ')';

            $esSubmissions = $submissions->where('employee_subject_id', $es->id);
            
            $approvedCount = $esSubmissions->where('status', 'approved')->count();
            $submittedCount = $esSubmissions->where('status', 'submitted')->count();
            $revisionCount = $esSubmissions->where('status', 'need_revision')->count();
            
            $missingCount = $expectedSubmissionsPerES - ($approvedCount + $submittedCount + $revisionCount);
            if ($missingCount < 0) $missingCount = 0;

            if ($missingCount > 0 || $revisionCount > 0) {
                if (!isset($teachersNotAchieved[$catName])) {
                    $teachersNotAchieved[$catName] = [];
                }
                if (!isset($teachersNotAchieved[$catName][$teacherName])) {
                    $teachersNotAchieved[$catName][$teacherName] = [];
                }
                $details = [];
                if ($missingCount > 0) $details[] = "Not Submitted: $missingCount";
                if ($revisionCount > 0) $details[] = "Need Revision: $revisionCount";
                
                $teachersNotAchieved[$catName][$teacherName][] = $subjectDisplay . " - " . implode(', ', $details);
            }

            foreach ($esSubmissions->where('status', 'submitted') as $sub) {
                $level = $sub->current_approval_level;
                $approver = $approvers->where('subject_id', $es->subject_id)
                                      ->where('school_class_id', $es->school_class_id)
                                      ->where('level', $level)
                                      ->first();
                
                // Fallback to null school_class_id if exact match not found
                if (!$approver) {
                    $approver = $approvers->where('subject_id', $es->subject_id)
                                          ->where('school_class_id', null)
                                          ->where('level', $level)
                                          ->first();
                }
                
                if ($approver && $approver->employee && $approver->employee->user) {
                    $approverName = $approver->employee->user->name;
                    $monthName = $sub->lessonPlanTargetMonth->month_name ?? '';
                    $weekName = $sub->week_number;
                    
                    if (!isset($approversPending[$catName])) {
                        $approversPending[$catName] = [];
                    }
                    if (!isset($approversPending[$catName][$approverName])) {
                        $approversPending[$catName][$approverName] = [];
                    }
                    
                    $approversPending[$catName][$approverName][] = "Teacher: $teacherName, Subject: $subjectDisplay, Month: $monthName, Week: $weekName";
                }
            }
        }

        $messageText = "*Lesson Plan Monitoring Report*\n";
        $messageText .= "Target: " . $target->title . "\n\n";
        
        $messageText .= "*Teachers Not Achieved (Revision / Not Submitted):*\n";
        if (empty($teachersNotAchieved)) {
            $messageText .= "- All achieved.\n";
        } else {
            foreach ($teachersNotAchieved as $catName => $teachers) {
                $messageText .= "\n*[{$catName}]*\n";
                foreach ($teachers as $teacher => $subjects) {
                    $messageText .= "• *$teacher*\n";
                    foreach ($subjects as $subj) {
                        $messageText .= "   - $subj\n";
                    }
                }
            }
        }

        $messageText .= "\n*Pending Approvals (Submitted but not reviewed):*\n";
        if (empty($approversPending)) {
            $messageText .= "- No pending approvals.\n";
        } else {
            foreach ($approversPending as $catName => $approversList) {
                $messageText .= "\n*[{$catName}]*\n";
                foreach ($approversList as $approver => $items) {
                    $messageText .= "• *$approver*\n";
                    foreach ($items as $item) {
                        $messageText .= "   - $item\n";
                    }
                }
            }
        }

        $maxLength = 5000;
        $messages = [];
        
        if (strlen($messageText) > $maxLength) {
            $lines = explode("\n", $messageText);
            $currentMessage = "";
            $part = 1;
            foreach ($lines as $line) {
                if (strlen($currentMessage) + strlen($line) + 1 > $maxLength) {
                    $messages[] = $currentMessage . "\n_(Continued in next message)_";
                    $part++;
                    $currentMessage = "*Lesson Plan Monitoring Report (Part $part)*\n" . $line . "\n";
                } else {
                    $currentMessage .= $line . "\n";
                }
            }
            if (!empty(trim($currentMessage))) {
                $messages[] = $currentMessage;
            }
        } else {
            $messages[] = $messageText;
        }

        foreach ($validNumbers as $targetNumber) {
            foreach ($messages as $msg) {
                $data = [
                    'api_key' => '7bd77f56d1e7fc38a07739594d0b4b7c0f0e594c',
                    'sender'  => '325293',
                    'number'  => $targetNumber,
                    'message' => $msg
                ];

                $curl = curl_init();
                curl_setopt_array($curl, array(
                  CURLOPT_URL => "https://mhisnetshield.us/apiv2/send-message.php",
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "POST",
                  CURLOPT_POSTFIELDS => json_encode($data)
                ));

                $response = curl_exec($curl);
                curl_close($curl);
            }
        }

        return redirect()->back()->with('success', 'WhatsApp Notification sent successfully!');
    }
}

