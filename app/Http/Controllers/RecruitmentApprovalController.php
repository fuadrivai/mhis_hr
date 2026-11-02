<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Services\RecruitmentOfferService;
use Illuminate\Http\Request;

class RecruitmentApprovalController extends Controller
{
    protected RecruitmentOfferService $offerService;

    public function __construct(RecruitmentOfferService $offerService)
    {
        $this->offerService = $offerService;
    }

    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->employee) {
            abort(403, 'Only employees with assigned system profiles can access approvals.');
        }

        $pendingSteps = $this->offerService->getMyPendingApprovals($user);

        return view('recruitment.my-approvals.index', [
            'title'        => 'My Offer Approvals',
            'pendingSteps' => $pendingSteps,
            'user'         => $user,
        ]);
    }

    public function approve(Request $request, Offer $offer)
    {
        $user = auth()->user();
        $employeeId = $user->employee?->id;
        if (!$employeeId) {
            abort(403, 'Employee profile required.');
        }

        $comment = $request->input('comment');
        $this->offerService->approveStep($offer, $employeeId, $comment, $user);

        return redirect()->route('recruitment.my-approvals.index')
            ->with('success', 'Offer approved successfully.');
    }

    public function reject(Request $request, Offer $offer)
    {
        $user = auth()->user();
        $employeeId = $user->employee?->id;
        if (!$employeeId) {
            abort(403, 'Employee profile required.');
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $this->offerService->rejectStep($offer, $employeeId, $validated['comment'], $user);

        return redirect()->route('recruitment.my-approvals.index')
            ->with('success', 'Offer rejected and returned to draft for revision.');
    }
}

