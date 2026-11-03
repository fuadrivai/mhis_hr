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
            'document_url'    => 'nullable|string|max:2048',
            'assessment_file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if (!$request->filled('document_url') && !$request->hasFile('assessment_file')) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Please provide a document link (Google Docs, Google Drive, PDF link) or upload a PDF report.');
        }

        try {
            $this->assessmentService->uploadAssessment(
                $application,
                [
                    'type'         => 'psychotest',
                    'assessed_at'  => $request->input('assessed_at'),
                    'conclusion'   => $request->input('conclusion'),
                    'notes'        => $request->input('notes'),
                    'document_url' => $request->input('document_url'),
                ],
                $request->file('assessment_file'),
                $request->user()
            );

            return redirect()
                ->route('recruitment.candidates.show', ['candidate' => $candidate->id, 'application' => $application->id])
                ->with('success', 'Psychotest assessment report saved successfully.');
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

        if ($assessment->document_url) {
            return redirect()->away($assessment->document_url);
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
