<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\Offer;
use App\Services\RecruitmentOfferService;
use Illuminate\Http\Request;

class RecruitmentOfferController extends Controller
{
    protected RecruitmentOfferService $offerService;

    public function __construct(RecruitmentOfferService $offerService)
    {
        $this->offerService = $offerService;
    }

    public function store(Request $request, Candidate $candidate, CandidateApplication $application)
    {
        $validated = $request->validate([
            'position_title'      => 'required|string|max:255',
            'grade_level'         => 'nullable|string|max:100',
            'basic_salary'        => 'required|numeric|min:0',
            'allowances'          => 'nullable|array',
            'other_benefits'      => 'nullable|string|max:2000',
            'proposed_start_date' => 'nullable|date',
            'valid_until'         => 'nullable|date|after_or_equal:today',
            'offer_letter_url'    => 'nullable|url|max:2000',
            'notes'               => 'nullable|string|max:2000',
            'submit_for_approval' => 'nullable|boolean',
            'approvers'           => 'nullable|array',
            'approvers.*.level'   => 'required_with:approvers|integer|min:1',
            'approvers.*.employee_id' => 'required_with:approvers|integer|exists:employees,id',
        ]);

        $this->offerService->createOffer($application, $validated, auth()->user());

        return redirect()->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Job offer draft created successfully.');
    }

    public function update(Request $request, Candidate $candidate, CandidateApplication $application, Offer $offer)
    {
        $validated = $request->validate([
            'position_title'      => 'required|string|max:255',
            'grade_level'         => 'nullable|string|max:100',
            'basic_salary'        => 'required|numeric|min:0',
            'allowances'          => 'nullable|array',
            'other_benefits'      => 'nullable|string|max:2000',
            'proposed_start_date' => 'nullable|date',
            'valid_until'         => 'nullable|date',
            'offer_letter_url'    => 'nullable|url|max:2000',
            'notes'               => 'nullable|string|max:2000',
            'submit_for_approval' => 'nullable|boolean',
            'approvers'           => 'nullable|array',
            'approvers.*.level'   => 'required_with:approvers|integer|min:1',
            'approvers.*.employee_id' => 'required_with:approvers|integer|exists:employees,id',
        ]);

        $this->offerService->updateOffer($offer, $validated, auth()->user());

        return redirect()->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Job offer updated successfully.');
    }

    public function submit(Request $request, Candidate $candidate, CandidateApplication $application, Offer $offer)
    {
        $this->offerService->submitForApproval($offer, auth()->user());

        return redirect()->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Offer submitted for approval.');
    }

    public function markSent(Request $request, Candidate $candidate, CandidateApplication $application, Offer $offer)
    {
        $validated = $request->validate([
            'sent_at'          => 'nullable|date',
            'offer_letter_url' => 'nullable|url|max:2000',
        ]);

        $this->offerService->markAsSent($offer, $validated, auth()->user());

        return redirect()->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Offer marked as sent to candidate.');
    }

    public function recordResponse(Request $request, Candidate $candidate, CandidateApplication $application, Offer $offer)
    {
        $validated = $request->validate([
            'status'         => 'required|in:accepted,declined,negotiating',
            'decline_reason' => 'nullable|required_if:status,declined|string|max:1000',
        ]);

        $this->offerService->recordCandidateResponse($offer, $validated['status'], $validated['decline_reason'] ?? null, auth()->user());

        return redirect()->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Candidate offer response recorded.');
    }

    public function cancel(Request $request, Candidate $candidate, CandidateApplication $application, Offer $offer)
    {
        $reason = $request->input('reason');
        $this->offerService->cancelOffer($offer, $reason, auth()->user());

        return redirect()->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Offer cancelled.');
    }

    public function printSummary(Candidate $candidate, CandidateApplication $application, Offer $offer)
    {
        $user = auth()->user();
        if (!$user || (!$user->canAccessRecruitment() && !$offer->canViewCompensation($user))) {
            abort(403, 'Unauthorized access to offer summary.');
        }

        return view('recruitment.offers.print', [
            'candidate'   => $candidate,
            'application' => $application,
            'offer'       => $offer,
            'user'        => $user,
        ]);
    }
}

