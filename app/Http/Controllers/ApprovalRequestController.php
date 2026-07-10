<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Services\ApprovalRequestService;
use App\Services\EmployeeService;
use App\Services\TimeOffService;
use Illuminate\Http\Request;
use Yajra\DataTables\Utilities\Request as UtilitiesRequest;

class ApprovalRequestController extends Controller
{
    private EmployeeService $employeeService;
    private TimeOffService $timeOffService;
    private ApprovalRequestService $approvalRequestService;

    public function __construct(EmployeeService $employeeService, TimeOffService $timeOffService, ApprovalRequestService $approvalRequestService)
    {
        $this->employeeService = $employeeService;
        $this->timeOffService = $timeOffService;
        $this->approvalRequestService = $approvalRequestService;
    }

    public function index()
    {
        return view('approval.request.index', [
            'title' => 'Approval Requests',
        ]);
    }

    public function dataTable(UtilitiesRequest $request)
    {
        $approvalRequests = ApprovalRequest::with([
            'type',
            'data',
            'approvals.approver',
            'approvals.approver.personal',
            'requester.personal',
            'requester.employment',
            'approval_rule'
        ])->select('approval_requests.*');

        if ($request->ajax()) {
            return datatables()->of($approvalRequests)->make(true);
        }
    }

    public function create()
    {
        $employees = $this->employeeService->getActive()->get()->load('personal');
        $timeoffs = $this->timeOffService->get();

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
            'attachments.*' => 'nullable|file|max:10240'
        ]);
        $validated['attachments'] = $request->file('attachments', []);

        $approvalRequest = $this->approvalRequestService->post($validated);
        return response()->json(['message' => 'Approval request created successfully', 'data' => $approvalRequest->load('approvals.approver.personal', 'requester.personal')], 201);
    }

    public function show($id)
    {
        $appRequest =  $this->approvalRequestService->show($id);
        $appRequest->load([
            'type',
            'data',
            'approvals.approver',
            'approvals.approver.personal',
            'requester.personal',
            'requester.employment',
            'approval_rule'
        ]);
        return response()->json($appRequest);
    }

    public function history($id)
    {
        $history =  $this->approvalRequestService->show($id)->histories()->with('approver.personal')->get();
        return response()->json($history);
    }
    public function approver($id)
    {
        $approvals =  $this->approvalRequestService->show($id)->approvals()->with('approver.personal')->get();
        return response()->json($approvals);
    }
    public function edit(ApprovalRequest $request)
    {
        $employees = $this->employeeService->getActive()->get()->load('personal');
        $timeoffs = $this->timeOffService->get();
        return view('approval.request.form', [
            'title' => 'Edit Approval Request',
            'approvalRequest' => $request,
            'employees' => $employees,
            'timeoffs' => $timeoffs,

        ]);
    }
}
