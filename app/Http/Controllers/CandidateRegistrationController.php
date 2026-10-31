<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\CvMatchResult;
use App\Services\CandidateRegistrationService;
use App\Services\Implement\CandidateRegistrationImplement;
use App\Services\Recruitment\Ai\CvMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateRegistrationController extends Controller
{
    public function __construct(
        private readonly CandidateRegistrationService $candidateRegistrationService,
        private readonly CvMatchingService $cvMatchingService,
    ) {
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $filters = $this->candidateIndexFilters($request);
        $candidates = $this->candidateRegistrationService->getCandidateIndexData($user, $filters);
        $filterData = $this->candidateRegistrationService->getCandidateIndexFilterData($user);

        return view('recruitment.candidate-index', array_merge($filterData, [
            'candidates' => $candidates,
            'filters' => $filters,
            'title' => 'Candidate Database',
            'section' => 'candidates',
        ]));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $filters = $this->candidateIndexFilters($request);

        return $this->candidateRegistrationService->exportCandidateIndex($user, $filters);
    }

    public function show(Candidate $candidate)
    {
        $candidate = $this->candidateRegistrationService->getCandidateDetails($candidate, auth()->user());
        $filterData = $this->candidateRegistrationService->getCandidateIndexFilterData(auth()->user());

        return view('recruitment.candidate-show', [
            'candidate' => $candidate,
            'assignableHrPics' => $filterData['assignableHrPics'],
            'stages' => $filterData['stages'],
            'title' => 'Candidate Profile: ' . $candidate->full_name,
            'section' => 'candidates',
        ]);
    }

    public function edit(Candidate $candidate)
    {
        $candidate = $this->candidateRegistrationService->getCandidateDetails($candidate, auth()->user());

        return view('recruitment.candidate-edit', [
            'candidate' => $candidate,
            'title' => 'Edit Candidate: ' . $candidate->full_name,
            'section' => 'candidates',
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
            'stage' => 'required|in:' . implode(',', array_keys(CandidateRegistrationImplement::STAGES)),
            'stage_substatus' => 'nullable|string|max:50',
            'reason' => 'nullable|string|max:1000',
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

    public function form(Request $request)
    {
        $rawTab = $request->query('tab', old('active_tab', 'add'));
        $activeTab = in_array($rawTab, ['bulk', 'bulk_cv'], true) ? 'bulk_cv' : $rawTab;

        return $this->candidateFormView([
            'candidate' => null,
            'activeTab' => $activeTab,
        ]);
    }

    public function bulkUpload(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $validated = $request->validate([
            'cv_files'       => 'required|array|min:1|max:50',
            'cv_files.*'     => 'required|file|mimes:pdf,doc,docx,txt,png,jpg,jpeg|max:10240',
            'job_opening_id' => 'required|integer|exists:job_openings,id',
            'source'         => 'required|in:' . implode(',', $this->candidateRegistrationService->getSources()),
            'source_detail'  => 'nullable|string|max:255',
            'consent'        => 'accepted',
        ]);

        $openingId = (int) $validated['job_opening_id'];
        $source = $validated['source'];
        $sourceDetail = $validated['source_detail'] ?? null;

        $successful = [];
        $failed = [];
        $seenEmails = [];

        foreach ($request->file('cv_files') as $file) {
            $filename = $file->getClientOriginalName();

            $extracted = [];
            try {
                $extracted = $this->cvMatchingService->parseCvFile($file);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("CV parse failed for {$filename}: " . $e->getMessage());
            }

            $fullName = trim($extracted['full_name'] ?? '');
            if (empty($fullName)) {
                $rawName = pathinfo($filename, PATHINFO_FILENAME);
                $fullName = ucwords(trim(preg_replace('/[_\-\.\d]+/', ' ', $rawName))) ?: 'Candidate';
            }

            $email = strtolower(trim($extracted['email'] ?? ''));
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $failed[] = [
                    'filename' => $filename,
                    'reason'   => 'Could not detect a valid email address in CV document.',
                ];
                continue;
            }

            if (in_array($email, $seenEmails, true)) {
                $failed[] = [
                    'filename' => $filename,
                    'reason'   => "Duplicate CV for email {$email} within the same batch.",
                ];
                continue;
            }
            $seenEmails[] = $email;

            $phone = trim($extracted['phone'] ?? '') ?: '-';
            $city = trim($extracted['current_city'] ?? '') ?: null;

            try {
                $application = $this->candidateRegistrationService->registerCandidate(
                    [
                        'full_name'      => $fullName,
                        'email'          => $email,
                        'phone'          => $phone,
                        'current_city'   => $city,
                        'job_opening_id' => $openingId,
                        'source'         => $source,
                        'source_detail'  => $sourceDetail,
                        'consent'        => 1,
                    ],
                    'hr',
                    $user,
                    $file
                );

                $successful[] = [
                    'filename'       => $filename,
                    'candidate_id'   => $application->candidate_id,
                    'candidate_code' => $application->candidate?->candidate_code,
                    'full_name'      => $fullName,
                    'email'          => $email,
                    'phone'          => $phone,
                    'current_city'   => $city,
                    'profile_url'    => route('recruitment.candidates.show', $application->candidate_id),
                ];
            } catch (\Illuminate\Validation\ValidationException $ve) {
                $firstMsg = array_values($ve->errors())[0][0] ?? 'Validation error';
                $failed[] = [
                    'filename' => $filename,
                    'reason'   => $firstMsg,
                ];
            } catch (\Throwable $e) {
                $failed[] = [
                    'filename' => $filename,
                    'reason'   => $e->getMessage(),
                ];
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'           => count($failed) === 0 ? 'success' : (count($successful) > 0 ? 'partial' : 'error'),
                'total'            => count($request->file('cv_files')),
                'successful_count' => count($successful),
                'failed_count'     => count($failed),
                'successful'       => $successful,
                'failed'           => $failed,
            ]);
        }

        $successCount = count($successful);
        $failedCount = count($failed);

        if ($successCount > 0 && $failedCount === 0) {
            return redirect()
                ->route('recruitment.candidates')
                ->with('success', "Bulk upload completed: {$successCount} candidates registered successfully.");
        }

        $message = "Bulk upload completed: {$successCount} registered, {$failedCount} failed.";
        return redirect()
            ->route('recruitment.candidates.form', ['tab' => 'bulk_cv'])
            ->with($successCount > 0 ? 'warning' : 'error', $message)
            ->with('bulk_results', [
                'successful' => $successful,
                'failed'     => $failed,
            ]);
    }

    public function bulkUploadItem(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $validated = $request->validate([
            'cv_file'        => 'required|file|mimes:pdf,doc,docx,txt,png,jpg,jpeg|max:10240',
            'job_opening_id' => 'required|integer|exists:job_openings,id',
            'source'         => 'required|in:' . implode(',', $this->candidateRegistrationService->getSources()),
            'source_detail'  => 'nullable|string|max:255',
            'consent'        => 'accepted',
            'full_name'      => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'current_city'   => 'nullable|string|max:255',
        ]);

        $file = $request->file('cv_file');
        $filename = $file->getClientOriginalName();

        $extracted = [];
        try {
            $extracted = $this->cvMatchingService->parseCvFile($file);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("CV parse failed for item {$filename}: " . $e->getMessage());
        }

        $fullName = trim($validated['full_name'] ?? $extracted['full_name'] ?? '');
        if (empty($fullName)) {
            $rawName = pathinfo($filename, PATHINFO_FILENAME);
            $fullName = ucwords(trim(preg_replace('/[_\-\.\d]+/', ' ', $rawName))) ?: 'Candidate';
        }

        $email = strtolower(trim($validated['email'] ?? $extracted['email'] ?? ''));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status'    => 'error',
                'code'      => 'MISSING_EMAIL',
                'filename'  => $filename,
                'message'   => 'Could not detect a valid email in CV. Please enter candidate email.',
                'extracted' => [
                    'full_name'    => $fullName,
                    'phone'        => $extracted['phone'] ?? '',
                    'current_city' => $extracted['current_city'] ?? '',
                ],
            ], 422);
        }

        $phone = trim($validated['phone'] ?? $extracted['phone'] ?? '') ?: '-';
        $city = trim($validated['current_city'] ?? $extracted['current_city'] ?? '') ?: null;

        try {
            $application = $this->candidateRegistrationService->registerCandidate(
                [
                    'full_name'      => $fullName,
                    'email'          => $email,
                    'phone'          => $phone,
                    'current_city'   => $city,
                    'job_opening_id' => (int) $validated['job_opening_id'],
                    'source'         => $validated['source'],
                    'source_detail'  => $validated['source_detail'] ?? null,
                    'consent'        => 1,
                ],
                'hr',
                $user,
                $file
            );

            return response()->json([
                'status'         => 'success',
                'message'        => 'Candidate registered successfully.',
                'filename'       => $filename,
                'candidate_id'   => $application->candidate_id,
                'application_id' => $application->id,
                'candidate_code' => $application->candidate?->candidate_code,
                'full_name'      => $fullName,
                'email'          => $email,
                'phone'          => $phone,
                'current_city'   => $city,
                'profile_url'    => route('recruitment.candidates.show', $application->candidate_id),
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $firstMsg = array_values($ve->errors())[0][0] ?? 'Validation error';
            return response()->json([
                'status'   => 'error',
                'code'     => 'VALIDATION_FAILED',
                'filename' => $filename,
                'message'  => $firstMsg,
                'errors'   => $ve->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status'   => 'error',
                'code'     => 'SERVER_ERROR',
                'filename' => $filename,
                'message'  => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'current_city' => 'nullable|string|max:255',
            'job_opening_id' => 'nullable|integer|exists:job_openings,id',
            'position_applied' => 'nullable|string|max:255',
            'branch_id' => 'nullable|integer|exists:branches,id,is_active,1',
            'source' => 'required|in:' . implode(',', $this->candidateRegistrationService->getSources()),
            'source_detail' => 'nullable|string|max:255',
            'resume' => 'nullable|file|mimes:pdf,doc,docx,txt,png,jpg,jpeg|max:10240',
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
            'job_opening_id' => 'required|integer|exists:job_openings,id',
            'source_detail' => 'nullable|string|max:255',
            'resume' => 'nullable|file|mimes:pdf,doc,docx,txt,png,jpg,jpeg|max:10240',
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

    public function parseCv(Request $request, CvMatchingService $cvMatchingService)
    {
        $request->validate([
            'resume' => 'nullable|file|mimes:pdf,doc,docx,txt,png,jpg,jpeg|max:10240',
            'file'   => 'nullable|file|mimes:pdf,doc,docx,txt,png,jpg,jpeg|max:10240',
        ]);

        $file = $request->file('resume') ?? $request->file('file');
        if (!$file) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No CV file uploaded.',
            ], 422);
        }

        try {
            $identity = $cvMatchingService->parseCvFile($file);
            return response()->json([
                'status' => 'success',
                'data'   => $identity,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'CV Parsing failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function matchCv(Candidate $candidate, CandidateApplication $application, CvMatchingService $cvMatchingService)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        try {
            $result = $cvMatchingService->matchApplication($application, $user, forceRerun: true);

            if ($result->isCompleted()) {
                return back()->with('success', "AI CV Matching completed with score: {$result->total_score}/100.");
            } elseif ($result->isNeedsReview()) {
                return back()->with('warning', "AI CV Matching flagged for manual review: {$result->summary}");
            } else {
                return back()->with('error', "AI CV Matching could not complete: " . ($result->error_message ?? 'Check job requirement configuration.'));
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'AI CV Matching failed: ' . $e->getMessage());
        }
    }

    public function overrideCvMatch(Request $request, CvMatchResult $cvMatchResult, CvMatchingService $cvMatchingService)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessRecruitment()) {
            abort(403);
        }

        $validated = $request->validate([
            'override_score' => 'nullable|integer|min:0|max:100',
            'note'           => 'required|string|min:3|max:1000',
        ]);

        try {
            $cvMatchingService->overrideScore(
                $cvMatchResult,
                isset($validated['override_score']) ? (int) $validated['override_score'] : null,
                $validated['note'],
                $user
            );

            return back()->with('success', 'HR score override has been recorded successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to save override: ' . $e->getMessage());
        }
    }

    private function candidateIndexFilters(Request $request): array
    {
        return $request->validate([
            'q' => 'nullable|string|max:255',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'job_opening_id' => 'nullable|integer|exists:job_openings,id',
            'position' => 'nullable|string|max:255',
            'status' => 'nullable|in:received,in_progress,hired,rejected,withdrawn',
            'stage' => 'nullable|in:' . implode(',', array_keys(CandidateRegistrationImplement::STAGES)),
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
