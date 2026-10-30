<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Services\CandidateRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateRegistrationController extends Controller
{
    public function __construct(private CandidateRegistrationService $candidateRegistrationService)
    {
    }

    public function index()
    {
        $request = request();
        $filters = $this->candidateIndexFilters($request);

        return view('recruitment.candidate-index', [
            'title' => 'Candidate Database',
            'section' => 'candidates',
            'candidates' => $this->candidateRegistrationService->getCandidateIndexData(auth()->user(), $filters),
            'filters' => $filters,
            'filterData' => $this->candidateRegistrationService->getCandidateIndexFilterData(auth()->user()),
        ]);
    }

    public function export(Request $request)
    {
        return $this->candidateRegistrationService->exportCandidateIndex(
            auth()->user(),
            $this->candidateIndexFilters($request)
        );
    }

    public function show(Candidate $candidate)
    {
        return view('recruitment.candidate-show', [
            'title' => 'Candidate Profile',
            'section' => 'candidates',
            'candidate' => $this->candidateRegistrationService->getCandidateDetails(
                $candidate,
                auth()->user()
            ),
            'filterData' => $this->candidateRegistrationService->getCandidateIndexFilterData(auth()->user()),
        ]);
    }

    public function edit(Candidate $candidate)
    {
        $candidate = $this->candidateRegistrationService->getCandidateDetails($candidate, auth()->user());

        return view('recruitment.candidate-edit', [
            'title' => 'Edit Candidate',
            'section' => 'candidates',
            'candidate' => $candidate,
        ]);
    }

    public function update(Request $request, Candidate $candidate)
    {
        $this->candidateRegistrationService->getCandidateDetails($candidate, auth()->user());
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:candidates,email,' . $candidate->id,
            'phone' => 'required|string|max:50',
            'current_city' => 'nullable|string|max:255',
        ]);
        $this->candidateRegistrationService->updateCandidate($candidate, $validated, auth()->user());

        return redirect()
            ->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Candidate profile updated successfully.');
    }

    public function updateApplication(Request $request, Candidate $candidate, CandidateApplication $application)
    {
        $this->candidateRegistrationService->getCandidateDetails($candidate, auth()->user());
        $validated = $request->validate([
            'stage' => 'required|in:received,screening,shortlisted,interview,assessment,reference_check,offer,hired,rejected,withdrawn',
            'hr_pic_id' => 'nullable|integer|exists:users,id',
        ]);
        $this->candidateRegistrationService->updateCandidateApplication(
            $candidate,
            $application,
            $validated,
            auth()->user()
        );

        return redirect()
            ->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Application stage and HR PIC updated.');
    }

    public function addApplication(Candidate $candidate)
    {
        $candidate = $this->candidateRegistrationService->getCandidateDetails($candidate, auth()->user());

        return $this->candidateFormView([
            'candidate' => $candidate,
            'activeTab' => 'add',
        ]);
    }

    public function form()
    {
        return $this->candidateFormView([
            'candidate' => null,
            'activeTab' => old('active_tab', 'add'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'current_city' => 'nullable|string|max:255',
            'position_applied' => 'required|string|max:255',
            'branch_id' => 'required|integer|exists:branches,id,is_active,1',
            'source' => 'required|in:' . implode(',', $this->candidateRegistrationService->getSources()),
            'source_detail' => 'nullable|string|max:255',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'consent' => 'accepted',
        ]);
        $this->candidateRegistrationService->registerCandidate(
            $validated,
            'hr',
            auth()->user(),
            $request->file('resume')
        );

        return redirect()
            ->route('recruitment.candidates')
            ->with('success', 'Application saved successfully.');
    }

    public function previewImport(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:5120',
        ]);
        $user = auth()->user();
        $previousToken = session('candidate_import_token');
        $result = $this->candidateRegistrationService->previewImport($validated['file'], $user);

        if ($previousToken && $previousToken !== $result['token']) {
            $this->candidateRegistrationService->discardImportPreview($previousToken);
        }

        if ($result['token']) {
            session(['candidate_import_token' => $result['token']]);
        } else {
            session()->forget('candidate_import_token');
        }

        return $this->candidateFormView([
            'activeTab' => 'import',
            'importPreview' => $result['preview'],
            'importToken' => $result['token'],
        ]);
    }

    public function commitImport(Request $request)
    {
        $validated = $request->validate([
            'import_token' => 'required|string',
        ]);
        $sessionToken = session('candidate_import_token');
        if (
            !is_string($sessionToken)
            || !hash_equals($sessionToken, $validated['import_token'])
        ) {
            return redirect()
                ->route('recruitment.candidates.form')
                ->withInput(['active_tab' => 'import'])
                ->withErrors(['file' => 'The import preview session has expired. Please upload the file again.']);
        }

        $result = $this->candidateRegistrationService->commitImport(
            $sessionToken,
            auth()->user()
        );
        session()->forget('candidate_import_token');

        $message = $result['imported_count'] . ' candidates imported successfully.';
        if ($result['skipped_count'] > 0) {
            $message .= ' ' . $result['skipped_count'] . ' rows were skipped because they were invalid or duplicated.';
        }

        return redirect()
            ->route('recruitment.candidates')
            ->with('success', $message);
    }

    public function downloadImportTemplate()
    {
        $headings = $this->candidateRegistrationService->getImportTemplateHeadings();

        return response()->streamDownload(function () use ($headings) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $headings, ',', '"', '');
            fclose($output);
        }, 'candidate-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function resume(Candidate $candidate, CandidateApplication $application)
    {
        $path = $this->candidateRegistrationService->getResumePath(
            $candidate,
            $application,
            auth()->user()
        );

        return Storage::disk('local')->download($path);
    }

    public function selfRegistrationForm()
    {
        return view(
            'recruitment.self-registration',
            $this->candidateRegistrationService->getSelfRegistrationPageData()
        );
    }

    private function candidateFormView(array $additionalData = [])
    {
        return view('recruitment.candidate-form', array_merge(
            $this->candidateRegistrationService->getRegistrationPageData(auth()->user()),
            [
                'title' => 'Candidate Form',
                'section' => 'candidates',
                'selfRegistrationUrl' => route('recruitment.self-registration'),
                'candidate' => null,
            ],
            $additionalData
        ));
    }

    public function selfRegister(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'current_city' => 'nullable|string|max:255',
            'position_applied' => 'required|string|max:255',
            'branch_id' => 'required|integer|exists:branches,id,is_active,1',
            'source_detail' => 'nullable|string|max:255',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'consent' => 'accepted',
            'website' => 'prohibited',
        ]);
        $this->candidateRegistrationService->registerCandidate(
            $validated,
            'self_registration',
            null,
            $request->file('resume')
        );

        return view('recruitment.self-registration-success');
    }

    private function candidateIndexFilters(Request $request): array
    {
        return $request->validate([
            'q' => 'nullable|string|max:255',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'position' => 'nullable|string|max:255',
            'status' => 'nullable|in:received,in_progress,hired,rejected,withdrawn',
            'stage' => 'nullable|in:received,screening,shortlisted,interview,assessment,reference_check,offer,hired,rejected,withdrawn',
            'source' => 'nullable|string|max:100',
            'hr_pic_id' => 'nullable|integer|exists:users,id',
            'applied_from' => 'nullable|date',
            'applied_to' => 'nullable|date|after_or_equal:applied_from',
            'inactive_days' => 'nullable|in:30,60,90,180',
            'sort' => 'nullable|in:candidate,applied_at,position,branch,stage,last_activity_at,updated_at',
            'direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|in:25,50,100',
        ]);
    }
}
