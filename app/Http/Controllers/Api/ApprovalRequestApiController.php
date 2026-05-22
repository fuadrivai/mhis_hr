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
            'histories.approver.personal',
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
        $requests = $this->approvalRequestService->getRequestByUser($request)->load('type','data');
        return response()->json($requests);
    }
    public function getApprovalByUser(Request $request)
    {
        $request['user']= $request['user'];
        $requests = $this->approvalRequestService->getApprovalByUser($request)->load(
            'approvalRequest.type',
            'approvalRequest.data',
            'approvalRequest.requester.personal',
            'approvalRequest.requester.employment',
            'approver',
        );
        return response()->json($requests);
    }
    public function action(Request $request)
    {
        $request['user']= $request['user'];
        $requests = $this->approvalRequestService->action($request);
        return response()->json($requests);
    }
    public function cancel($id, Request $request)
    {
        $request['user']= $request['user'];
        $request['request_id'] = $id;
        $requests = $this->approvalRequestService->cancel($request);
        return response()->json($requests);
    }
}
