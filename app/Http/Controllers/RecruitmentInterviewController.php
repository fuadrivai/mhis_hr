<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\InterviewEvaluation;
use App\Models\InterviewRound;
use App\Services\RecruitmentInterviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class RecruitmentInterviewController extends Controller
{
    protected RecruitmentInterviewService $interviewService;

    public function __construct(RecruitmentInterviewService $interviewService)
    {
        $this->interviewService = $interviewService;
    }

    public function storeRound(Request $request, Candidate $candidate, CandidateApplication $application)
    {
        $request->validate([
            'round_type'       => 'required|in:hr,user,panel',
            'scheduled_at'     => 'required|date',
            'location_or_link' => 'nullable|string|max:500',
            'notes'            => 'nullable|string|max:2000',
            'interviewers'     => 'required|array|min:1',
            'interviewers.*'   => 'required|integer|exists:employees,id',
        ]);

        try {
            $this->interviewService->scheduleRound(
                $application,
                $request->only(['round_type', 'scheduled_at', 'location_or_link', 'notes', 'round_no']),
                $request->input('interviewers', []),
                $request->user()
            );

            return redirect()
                ->route('recruitment.candidates.show', ['candidate' => $candidate->id, 'application' => $application->id])
                ->with('success', 'Interview round scheduled successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function updateRound(
        Request $request,
        Candidate $candidate,
        CandidateApplication $application,
        InterviewRound $round
    ) {
        if ($round->candidate_application_id !== $application->id) {
            abort(404, 'Round not found for this application.');
        }

        $request->validate([
            'round_type'       => 'required|in:hr,user,panel',
            'scheduled_at'     => 'required|date',
            'location_or_link' => 'nullable|string|max:500',
            'notes'            => 'nullable|string|max:2000',
            'interviewers'     => 'nullable|array|min:1',
            'interviewers.*'   => 'integer|exists:employees,id',
        ]);

        try {
            $this->interviewService->updateRound(
                $round,
                $request->only(['round_type', 'scheduled_at', 'location_or_link', 'notes']),
                $request->input('interviewers'),
                $request->user()
            );

            return redirect()
                ->route('recruitment.candidates.show', ['candidate' => $candidate->id, 'application' => $application->id])
                ->with('success', 'Interview round updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function cancelRound(
        Request $request,
        Candidate $candidate,
        CandidateApplication $application,
        InterviewRound $round
    ) {
        if ($round->candidate_application_id !== $application->id) {
            abort(404, 'Round not found for this application.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->interviewService->cancelRound($round, $request->input('reason'), $request->user());

            return redirect()
                ->route('recruitment.candidates.show', ['candidate' => $candidate->id, 'application' => $application->id])
                ->with('success', 'Interview round cancelled.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function reopenEvaluation(
        Request $request,
        Candidate $candidate,
        CandidateApplication $application,
        InterviewEvaluation $evaluation
    ) {
        if ($evaluation->interviewRound->candidate_application_id !== $application->id) {
            abort(404, 'Evaluation not found for this application.');
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->interviewService->reopenEvaluation($evaluation, $request->input('reason'), $request->user());

            return redirect()
                ->route('recruitment.candidates.show', ['candidate' => $candidate->id, 'application' => $application->id])
                ->with('success', 'Interview evaluation reopened for modification.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function uploadAttachment(
        Request $request,
        Candidate $candidate,
        CandidateApplication $application,
        InterviewEvaluation $evaluation
    ) {
        if ($evaluation->interviewRound->candidate_application_id !== $application->id) {
            abort(404, 'Evaluation not found for this application.');
        }

        $request->validate([
            'attachment' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        try {
            $this->interviewService->uploadManualEvaluationAttachment(
                $evaluation,
                $request->file('attachment'),
                $request->user()
            );

            return redirect()
                ->route('recruitment.candidates.show', ['candidate' => $candidate->id, 'application' => $application->id])
                ->with('success', 'Physical evaluation form attached successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function downloadAttachment(
        Request $request,
        Candidate $candidate,
        CandidateApplication $application,
        InterviewEvaluation $evaluation
    ): Response {
        if ($evaluation->interviewRound->candidate_application_id !== $application->id) {
            abort(404, 'Evaluation not found for this application.');
        }

        $user = $request->user();
        if (!$user->canAccessRecruitment()) {
            // Check if user is the assigned interviewer
            $isAssigned = $user->employee && $evaluation->interviewer_employee_id === $user->employee->id;
            if (!$isAssigned) {
                abort(403, 'Unauthorized access to evaluation attachment.');
            }
        }

        if (!$evaluation->attachment_path || !Storage::disk('local')->exists($evaluation->attachment_path)) {
            abort(404, 'Evaluation attachment not found.');
        }

        return Storage::disk('local')->response(
            $evaluation->attachment_path,
            $evaluation->original_attachment_name ?? 'evaluation-attachment'
        );
    }
}

