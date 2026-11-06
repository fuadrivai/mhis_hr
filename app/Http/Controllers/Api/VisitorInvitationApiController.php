<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorVisitRequest;
use App\Services\VisitorManagementService;
use Illuminate\Http\JsonResponse;

class VisitorInvitationApiController extends Controller
{
    public function __construct(private VisitorManagementService $visitorService)
    {
    }

    public function store(StoreVisitorVisitRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('visitor-photos');
        }

        $created = $this->visitorService->createVisit($request->user(), $data, 'employee_invitation');
        $visit = $created['visit'];

        return response()->json([
            'message' => $visit->status === 'pending_confirmation'
                ? 'Visitor invitation submitted for host approval.'
                : 'Visitor invitation created.',
            'data' => [
                'visit_code' => $visit->visit_code,
                'status' => $visit->status,
                'visit_date' => $visit->visit_date->toDateString(),
                'expected_arrival_at' => $visit->expected_arrival_at?->toIso8601String(),
                'expected_departure_at' => $visit->expected_departure_at?->toIso8601String(),
                'branch_id' => $visit->branch_id,
                'host_employee_id' => $visit->host_employee_id,
            ],
        ], $visit->status === 'pending_confirmation' ? 202 : 201);
    }
}
