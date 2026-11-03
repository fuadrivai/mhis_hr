<?php

namespace App\Http\Controllers;

use App\Models\EvaluationCompetency;
use App\Models\InterviewRound;
use App\Services\RecruitmentInterviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class RecruitmentMyInterviewController extends Controller
{
    protected RecruitmentInterviewService $interviewService;

    public function __construct(RecruitmentInterviewService $interviewService)
    {
        $this->interviewService = $interviewService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Your account is not linked to an employee record.');
        }

        $rounds = $this->interviewService->getInterviewerAssignedRounds($employee);

        return view('recruitment.my-interviews.index', [
            'rounds'   => $rounds,
            'employee' => $employee,
            'title'    => 'My Assigned Interviews',
        ]);
    }

    public function show(Request $request, InterviewRound $round)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Your account is not linked to an employee record.');
        }

        // Interviewer isolation: Interviewer can only access rounds assigned to them
        $isAssigned = $round->interviewers()->where('employees.id', $employee->id)->exists();
        if (!$isAssigned && !$user->isRecruitmentAdministrator() && !$user->isRecruitmentMonitor()) {
            abort(403, 'You are not assigned to this interview round.');
        }

        $myEvaluation = $round->evaluations()
            ->where('interviewer_employee_id', $employee->id)
            ->with('scores')
            ->first();

        $activeCompetencies = EvaluationCompetency::active()->get();

        // Blind review: peer evaluations are ONLY available if the interviewer has submitted their evaluation
        $peerSummary = null;
        if ($myEvaluation && $myEvaluation->status === 'submitted') {
            $peerSummary = $this->interviewService->getEvaluationSummaryForInterviewer($round, $employee);
        }

        // Load candidate application with candidate info & assessments (without salary/sensitive fields)
        $application = $round->candidateApplication()->with(['candidate', 'jobOpening', 'branch', 'assessments.uploadedBy'])->first();

        return view('recruitment.my-interviews.evaluate', [
            'round'              => $round,
            'application'        => $application,
            'candidate'          => $application->candidate,
            'myEvaluation'       => $myEvaluation,
            'activeCompetencies' => $activeCompetencies,
            'peerSummary'        => $peerSummary,
            'existingScores'     => $myEvaluation ? $myEvaluation->scores->pluck('score', 'competency_id')->toArray() : [],
            'existingComments'   => $myEvaluation ? $myEvaluation->scores->pluck('comment', 'competency_id')->toArray() : [],
            'title'              => 'Interview Evaluation: ' . ($application->candidate->full_name ?? 'Candidate'),
        ]);
    }

    public function saveDraft(Request $request, InterviewRound $round)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Employee profile not found.');
        }

        try {
            $this->interviewService->saveEvaluationDraft(
                $round,
                $employee,
                $request->only(['overall_recommendation', 'strengths', 'concerns', 'notes', 'scores', 'comments']),
                $request->file('attachment')
            );

            return redirect()
                ->route('recruitment.my-interviews.show', $round->id)
                ->with('success', 'Evaluation draft saved successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function submit(Request $request, InterviewRound $round)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Employee profile not found.');
        }

        $request->validate([
            'overall_recommendation' => 'required|in:strongly_recommend,recommend,hold,not_recommend',
            'strengths'              => 'nullable|string|max:2000',
            'concerns'               => 'nullable|string|max:2000',
            'notes'                  => 'nullable|string|max:2000',
            'scores'                 => 'required|array',
            'scores.*'               => 'required|integer|min:1|max:5',
            'comments'               => 'nullable|array',
            'attachment'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        try {
            $this->interviewService->submitEvaluation(
                $round,
                $employee,
                $request->only(['overall_recommendation', 'strengths', 'concerns', 'notes', 'scores', 'comments']),
                $request->file('attachment')
            );

            return redirect()
                ->route('recruitment.my-interviews.show', $round->id)
                ->with('success', 'Interview recommendation submitted successfully. Thank you for your evaluation!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function downloadCandidateCv(Request $request, InterviewRound $round): Response
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Employee profile not found.');
        }

        $isAssigned = $round->interviewers()->where('employees.id', $employee->id)->exists();
        if (!$isAssigned && !$user->isRecruitmentAdministrator() && !$user->isRecruitmentMonitor()) {
            abort(403, 'Unauthorized to view candidate CV for this round.');
        }

        $application = $round->candidateApplication;
        if (!$application || !$application->resume_path) {
            abort(404, 'Candidate resume file not found.');
        }

        $fullPath = \App\Services\Implement\CandidateRegistrationImplement::resolveResumeFullPath($application->resume_path);
        if (!$fullPath || !file_exists($fullPath)) {
            abort(404, 'Candidate resume file not found on disk.');
        }

        $mime = is_file($fullPath) ? (mime_content_type($fullPath) ?: 'application/pdf') : 'application/pdf';
        $filename = $application->original_filename ?: basename($fullPath);

        return response()->file($fullPath, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
        ]);
    }

    public function viewCandidateAssessment(
        Request $request,
        InterviewRound $round,
        \App\Models\ApplicationAssessment $assessment
    ): Response {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Employee profile not found.');
        }

        $isAssigned = $round->interviewers()->where('employees.id', $employee->id)->exists();
        if (!$isAssigned && !$user->isRecruitmentAdministrator() && !$user->isRecruitmentMonitor()) {
            abort(403, 'Unauthorized to view candidate assessment for this round.');
        }

        if ($assessment->candidate_application_id !== $round->candidate_application_id) {
            abort(404, 'Assessment not found for this candidate application.');
        }

        if ($assessment->document_url) {
            return redirect()->away($assessment->document_url);
        }

        if (!$assessment->file_path || !Storage::disk('local')->exists($assessment->file_path)) {
            abort(404, 'Assessment report file not found on private storage.');
        }

        $fullPath = Storage::disk('local')->path($assessment->file_path);
        $mime = is_file($fullPath) ? (mime_content_type($fullPath) ?: 'application/pdf') : 'application/pdf';
        $filename = $assessment->original_filename ?: 'psychotest-report.pdf';

        return response()->file($fullPath, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
        ]);
    }
}
