<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Services\ApprovalRequestService;
use App\Services\EmployeeService;
use App\Services\TimeOffService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

    public function timeoffAction(Request $request)
    {
        return view('approval.email-action');
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
        logger()->info('ApprovalRequest store endpoint hit', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => optional(auth()->user())->id,
            'referer' => $request->headers->get('referer'),
            'user_agent' => $request->userAgent(),
        ]);

        $validated = $request->validate([
            'requester_employee_id' => 'required|exists:employees,id',
            'timeoff_id' => 'required|exists:timeoffs,id',
            'note' => 'nullable|string',
            'dynamic_fields' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:10240'
        ]);
        $validated['attachments'] = $request->file('attachments', []);

        // Prevent duplicate inserts when the same POST is replayed by browser refresh/back.
        $requestFingerprint = sha1(json_encode([
            'user_id' => optional(auth()->user())->id,
            'requester_employee_id' => Arr::get($validated, 'requester_employee_id'),
            'timeoff_id' => Arr::get($validated, 'timeoff_id'),
            'note' => Arr::get($validated, 'note'),
            'dynamic_fields' => Arr::get($validated, 'dynamic_fields', []),
            'attachments' => collect($validated['attachments'])
                ->map(function ($file) {
                    return $file->getClientOriginalName() . ':' . $file->getSize();
                })->values()->all(),
        ]));

        $lastFingerprint = $request->session()->get('approval_request_last_fingerprint');
        $lastAt = (int) $request->session()->get('approval_request_last_at', 0);

        if ($lastFingerprint === $requestFingerprint && (time() - $lastAt) <= 15) {
            logger()->warning('Duplicate approval request submission blocked', [
                'user_id' => optional(auth()->user())->id,
                'requester_employee_id' => Arr::get($validated, 'requester_employee_id'),
                'timeoff_id' => Arr::get($validated, 'timeoff_id'),
            ]);

            return redirect('/time/request')->with('success', 'Duplicate submission was blocked.');
        }

        $this->approvalRequestService->post($validated);

        $request->session()->put('approval_request_last_fingerprint', $requestFingerprint);
        $request->session()->put('approval_request_last_at', time());

        return redirect('/time/request')->with('success', 'Approval request submitted successfully.');
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
