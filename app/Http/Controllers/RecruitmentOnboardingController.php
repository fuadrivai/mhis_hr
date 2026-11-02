<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Services\RecruitmentOnboardingService;
use Illuminate\Http\Request;

class RecruitmentOnboardingController extends Controller
{
    protected RecruitmentOnboardingService $onboardingService;

    public function __construct(RecruitmentOnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    public function updateContract(Request $request, Candidate $candidate, CandidateApplication $application)
    {
        $validated = $request->validate([
            'contract_status' => 'required|in:pending,sent,signed',
            'contract_url'    => 'nullable|url|max:2000',
            'notes'           => 'nullable|string|max:1000',
            'contract_file'   => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $this->onboardingService->updateContract(
            $application,
            $validated,
            $request->file('contract_file'),
            auth()->user()
        );

        return redirect()->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Contract details updated successfully.');
    }

    public function downloadContract(Candidate $candidate, CandidateApplication $application)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403, 'Unauthorized access to contract document.');
        }

        $onboarding = $application->onboarding;
        if (!$onboarding || !$onboarding->contract_file_path) {
            abort(404, 'No contract document uploaded.');
        }

        $fullPath = $this->onboardingService->getContractFilePath($onboarding);
        if (!$fullPath) {
            abort(404, 'Contract file not found in storage.');
        }

        return response()->download($fullPath, 'Signed_Contract_' . ($candidate->candidate_code ?: 'Candidate') . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function updateJoin(Request $request, Candidate $candidate, CandidateApplication $application)
    {
        $validated = $request->validate([
            'join_status'  => 'required|in:pending,joined,no_show,postponed',
            'join_date'    => 'nullable|date',
            'postponed_to' => 'nullable|date',
            'reason'       => 'nullable|string|max:1000',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $this->onboardingService->updateJoinStatus(
            $application,
            $validated,
            auth()->user()
        );

        return redirect()->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Candidate joining status updated successfully.');
    }

    public function createDraft(Request $request, Candidate $candidate, CandidateApplication $application)
    {
        $employee = $this->onboardingService->createEmployeeDraft($application, auth()->user());

        return redirect()->route('recruitment.candidates.show', $candidate)
            ->with('success', "Employee draft #{$employee->id} created successfully! You can now complete the employee profile.")
            ->with('employee_draft_id', $employee->id);
    }
}

