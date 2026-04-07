<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\ApprovalHistory;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestAttachment;
use App\Models\ApprovalRequestData;
use App\Models\Employee;
use App\Models\TimeOff;
use App\Services\ApprovalEngine;
use App\Services\EmployeeService;
use App\Services\TimeOffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Utilities\Request as UtilitiesRequest;

class ApprovalRequestController extends Controller
{
    private ApprovalEngine $approvalEngine;
    private EmployeeService $employeeService;
    private TimeOffService $timeOffService;

    public function __construct(ApprovalEngine $approvalEngine, EmployeeService $employeeService, TimeOffService $timeOffService)
    {
        $this->approvalEngine = $approvalEngine;
        $this->employeeService = $employeeService;
        $this->timeOffService = $timeOffService;
    }

    public function index()
    {
        return view('approval.request.index', [
            'title' => 'Approval Requests',
        ]);
    }

    public function dataTable(UtilitiesRequest $request)
    {
        $approvalRequests = ApprovalRequest::with(['type', 'requester.personal','requester.employment', 'approval_rule'])->select('approval_requests.*');

        if ($request->ajax()) {
            return datatables()->of($approvalRequests)->make(true);
        }
    }

    public function create()
    {
        $employees = Employee::with('personal')->get();
        $timeoffs = TimeOff::all();

        return view('approval.request.form', [
            'title' => 'Create Approval Request',
            'employees' => $employees,
            'timeoffs' => $timeoffs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'requester_employee_id' => 'required|exists:employees,id',
            'timeoff_id' => 'required|exists:timeoffs,id',
            'note' => 'nullable|string',
            'dynamic_fields' => 'nullable|array',
            'dynamic_fields.*' => 'nullable',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($validated['requester_employee_id']);
            $timeoff = TimeOff::findOrFail($validated['timeoff_id']);
            $approvalRule = $this->approvalEngine->resolveApprovalRule($employee);

            if (!$approvalRule) {
                return back()->withErrors(['requester_employee_id' => 'No matching approval rule found for this employee.']);
            }

            $approvalRequest = ApprovalRequest::create([
                'approval_rule_id' => $approvalRule->id,
                'requester_employee_id' => $employee->id,
                'timeoff_id' => $timeoff->id,
                'note' => $validated['note'] ?? null,
                'current_step' => 1,
                'status' => 'pending',
            ]);

            ApprovalRequestData::create([
                'approval_request_id' => $approvalRequest->id,
                'data' => $validated['dynamic_fields'] ?? [],
            ]);

            foreach ($request->file('attachments', []) as $file) {
                $path = $file->store('approval-request-attachments');
                ApprovalRequestAttachment::create([
                    'approval_request_id' => $approvalRequest->id,
                    'field_name' => 'attachments',
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }

            foreach ($approvalRule->steps as $step) {
                Approval::create([
                    'approval_request_id' => $approvalRequest->id,
                    'step_order' => $step->step_order,
                    'approver_employee_id' => $step->approver_employee_id,
                    'approval_mode' => $step->approval_mode,
                    'status' => 'pending',
                ]);
            }

            ApprovalHistory::create([
                'approval_request_id' => $approvalRequest->id,
                'action' => 'submitted',
                'step_order' => 1,
            ]);

            DB::commit();
            return redirect('/time/request')->with('success', 'Approval request submitted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }

    public function edit(ApprovalRequest $request)
    {
        $employees = $this->employeeService->get();
        $timeoffs = $this->timeOffService->get();
        return view('approval.request.form', [
            'title' => 'Edit Approval Request',
            'approvalRequest' => $request,
            'employees' => $employees,
            'timeoffs' => $timeoffs,

        ]);
    }
}
