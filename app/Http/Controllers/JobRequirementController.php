<?php

namespace App\Http\Controllers;

use App\Models\JobRequirement;
use App\Services\JobRequirementService;
use Illuminate\Http\Request;

class JobRequirementController extends Controller
{
    public function __construct(private JobRequirementService $jobRequirementService)
    {
    }

    public function index()
    {
        return view('recruitment.settings.job-requirements.index', [
            'title' => 'Job Requirements',
            'section' => 'settings',
            'jobRequirements' => $this->jobRequirementService->getAll(auth()->user()),
        ]);
    }

    public function create()
    {
        $this->jobRequirementService->authorizeAccess(auth()->user());

        return view('recruitment.settings.job-requirements.create', [
            'title' => 'Add Job Requirement',
            'section' => 'settings',
        ]);
    }

    public function edit(JobRequirement $jobRequirement)
    {
        return view('recruitment.settings.job-requirements.create', [
            'title' => 'Edit Job Requirement',
            'section' => 'settings',
            'jobRequirement' => $this->jobRequirementService->find($jobRequirement, auth()->user()),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'footer' => 'nullable|string',
            'requirements' => 'required|array|min:1',
            'requirements.*' => 'required|string|max:1000',
        ]);

        $this->jobRequirementService->create($validated, auth()->user());

        return redirect()
            ->route('recruitment.settings.job-requirements')
            ->with('success', 'Job requirement added successfully.');
    }

    public function update(Request $request, JobRequirement $jobRequirement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'footer' => 'nullable|string',
            'requirements' => 'required|array|min:1',
            'requirements.*' => 'required|string|max:1000',
        ]);

        $this->jobRequirementService->update($jobRequirement, $validated, auth()->user());

        return redirect()
            ->route('recruitment.settings.job-requirements')
            ->with('success', 'Job requirement updated successfully.');
    }

    public function updateStatus(Request $request, JobRequirement $jobRequirement)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $this->jobRequirementService->setActive(
            $jobRequirement,
            (bool) $validated['is_active'],
            auth()->user()
        );

        return redirect()
            ->route('recruitment.settings.job-requirements')
            ->with('success', 'Job requirement status updated successfully.');
    }

    public function destroy(JobRequirement $jobRequirement)
    {
        $this->jobRequirementService->delete($jobRequirement, auth()->user());

        return redirect()
            ->route('recruitment.settings.job-requirements')
            ->with('success', 'Job requirement deleted successfully.');
    }
}
