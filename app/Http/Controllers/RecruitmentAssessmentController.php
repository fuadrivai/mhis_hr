<?php

namespace App\Http\Controllers;

use App\Models\ApplicationAssessment;
use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Services\RecruitmentAssessmentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecruitmentAssessmentController extends Controller
{
    protected RecruitmentAssessmentService $assessmentService;

    public function __construct(RecruitmentAssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    public function store(Request $request, Candidate $candidate, CandidateApplication $application)
    {
        $request->validate([
            'assessed_at'     => 'required|date',
            'conclusion'      => 'required|in:recommended,considered,not_recommended',
            'notes'           => 'nullable|string|max:2000',
            'assessment_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $this->assessmentService->uploadAssessment(
                $application,
                [
                    'type'        => 'psychotest',
                    'assessed_at' => $request->input('assessed_at'),
                    'conclusion'  => $request->input('conclusion'),
                    'notes'       => $request->input('notes'),
                ],
                $request->file('assessment_file'),
                $request->user()
            );

            return redirect()
                ->route('recruitment.candidates.show', ['candidate' => $candidate->id, 'application' => $application->id])
                ->with('success', 'Psychotest assessment report uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function download(
        Request $request,
        Candidate $candidate,
        CandidateApplication $application,
        ApplicationAssessment $assessment
    ): Response {
        if ($assessment->candidate_application_id !== $application->id) {
            abort(404, 'Assessment not found for this candidate application.');
        }

        return $this->assessmentService->streamAssessmentFile($assessment, $request->user());
    }

    public function destroy(
        Request $request,
        Candidate $candidate,
        CandidateApplication $application,
        ApplicationAssessment $assessment
    ) {
        if ($assessment->candidate_application_id !== $application->id) {
            abort(404, 'Assessment not found for this candidate application.');
        }

        try {
            $this->assessmentService->deleteAssessment($assessment, $request->user());

            return redirect()
                ->route('recruitment.candidates.show', ['candidate' => $candidate->id, 'application' => $application->id])
                ->with('success', 'Psychotest assessment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}

