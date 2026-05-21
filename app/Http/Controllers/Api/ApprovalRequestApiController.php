<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApprovalRequestService;
use Illuminate\Http\Request;

class ApprovalRequestApiController extends Controller
{
    private ApprovalRequestService $approvalRequestService;

    public function __construct(ApprovalRequestService $approvalRequestService)
    {
        $this->approvalRequestService = $approvalRequestService;
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

        return $this->approvalRequestService->post($validated);
    }

    public function show($id)
    {
        $request = $this->approvalRequestService->show($id)->load(
            'type',
            'data',
            'approval_rule',
            'approval_rule.steps',
            'approval_rule.branch',
            'approval_rule.organization',
            'approval_rule.level',
            'approval_rule.position',
            'requester.personal', 
            'requester.employment', 
            'approvals.approver.personal',
            'approvals.approver.employment',
            'approvals.approvalRequestData',
            'attachments',
            'histories'
            );
        return response()->json($request);
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

    public function getRequestByUser(Request $request)
    {
        $request['user']= $request['user'];
        $requests = $this->approvalRequestService->getByUser($request)->load('type','data');
        return response()->json($requests);
    }
}
