<?php

namespace App\Http\Controllers;

use App\Models\JobOpening;
use App\Services\JobOpeningService;
use Illuminate\Http\Request;

class JobOpeningController extends Controller
{
    public function __construct(
        private readonly JobOpeningService $jobOpeningService
    ) {
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $filters = $request->validate([
            'status'    => 'nullable|in:draft,open,closed,filled',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'search'    => 'nullable|string|max:255',
        ]);

        $openings = $this->jobOpeningService->getOpenings($filters, $user);
        $formData = $this->jobOpeningService->getFormData($user);

        return view('recruitment.job-openings.index', [
            'openings' => $openings,
            'branches' => $formData['branches'],
            'filters'  => $filters,
            'title'    => 'Job Openings',
            'section'  => 'openings',
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $formData = $this->jobOpeningService->getFormData($user);

        return view('recruitment.job-openings.create', array_merge($formData, [
            'title'   => 'Create Job Opening',
            'section' => 'openings',
        ]));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $validated = $request->validate([
            'job_requirement_id'         => 'nullable|integer|exists:job_requirements,id',
            'position_id'                => 'nullable|integer|exists:positions,id',
            'title'                      => 'required|string|max:255',
            'division'                   => 'nullable|string|max:100',
            'branch_id'                  => 'required|integer|exists:branches,id',
            'headcount'                  => 'required|integer|min:1',
            'hiring_manager_employee_id' => 'nullable|integer|exists:employees,id',
            'opened_at'                  => 'required|date',
            'closes_at'                  => 'nullable|date|after_or_equal:opened_at',
            'status'                     => 'required|in:draft,open',
        ]);

        $opening = $this->jobOpeningService->createOpening($validated, $user);

        return redirect()
            ->route('recruitment.job-openings.show', $opening)
            ->with('success', 'Job Opening created successfully.');
    }

    public function show(JobOpening $jobOpening)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $details = $this->jobOpeningService->getOpeningDetails($jobOpening, $user);

        return view('recruitment.job-openings.show', array_merge($details, [
            'title'   => 'Job Opening: ' . $jobOpening->title,
            'section' => 'openings',
        ]));
    }

    public function edit(JobOpening $jobOpening)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $formData = $this->jobOpeningService->getFormData($user);

        return view('recruitment.job-openings.edit', array_merge($formData, [
            'opening' => $jobOpening,
            'title'   => 'Edit Job Opening: ' . $jobOpening->title,
            'section' => 'openings',
        ]));
    }

    public function update(Request $request, JobOpening $jobOpening)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $validated = $request->validate([
            'job_requirement_id'         => 'nullable|integer|exists:job_requirements,id',
            'position_id'                => 'nullable|integer|exists:positions,id',
            'title'                      => 'required|string|max:255',
            'division'                   => 'nullable|string|max:100',
            'branch_id'                  => 'required|integer|exists:branches,id',
            'headcount'                  => 'required|integer|min:1',
            'hiring_manager_employee_id' => 'nullable|integer|exists:employees,id',
            'opened_at'                  => 'required|date',
            'closes_at'                  => 'nullable|date|after_or_equal:opened_at',
            'status'                     => 'required|in:draft,open,closed,filled',
        ]);

        $this->jobOpeningService->updateOpening($jobOpening, $validated, $user);

        return redirect()
            ->route('recruitment.job-openings.show', $jobOpening)
            ->with('success', 'Job Opening updated successfully.');
    }

    public function close(Request $request, JobOpening $jobOpening)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $validated = $request->validate([
            'closed_reason' => 'required|string|max:1000',
        ]);

        $this->jobOpeningService->closeOpening($jobOpening, $validated['closed_reason'], $user);

        return redirect()
            ->back()
            ->with('success', 'Job Opening has been closed.');
    }

    public function reopen(JobOpening $jobOpening)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $this->jobOpeningService->reopenOpening($jobOpening, $user);

        return redirect()
            ->back()
            ->with('success', 'Job Opening has been reopened.');
    }

    public function batchMatch(JobOpening $jobOpening, \App\Services\Recruitment\Ai\CvMatchingService $cvMatchingService)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $dispatched = $cvMatchingService->batchMatchOpening($jobOpening, $user);

        return redirect()
            ->back()
            ->with('success', 'Queued AI matching for ' . count($dispatched) . ' applicant(s) in this job opening.');
    }
}

