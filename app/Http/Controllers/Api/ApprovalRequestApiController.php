<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Services\ApprovalRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

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
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $validated['attachments'] = $request->file('attachments', []);

        try {
            $approvalRequest = $this->approvalRequestService->post($validated);

            return response()->json([
                'success' => true,
                'message' => 'Approval request submitted successfully.',
                'data' => $approvalRequest,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $request = $this->approvalRequestService->show($id);
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

    public function nonAuthAction(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'request_id' => 'required|integer|exists:approval_requests,id',
            'action' => 'required|in:approved,rejected',
            'note' => 'nullable|string',
        ]);

        $request->merge([
            'user' => ['id' => $validated['user_id']],
            'request_id' => $validated['request_id'],
            'action' => $validated['action'],
            'note' => $validated['note'] ?? null,
        ]);

        $this->approvalRequestService->action($request);
        return redirect('/timeoff/action');
    }
}
