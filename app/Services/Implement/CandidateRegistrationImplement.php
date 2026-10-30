<?php

namespace App\Services\Implement;

use App\Imports\CandidateImport;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\Position;
use App\Models\User;
use App\Services\CandidateRegistrationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CandidateRegistrationImplement implements CandidateRegistrationService
{
    private const SOURCES = [
        'Instagram',
        'LinkedIn',
        'Referral',
        'Email',
        'Walk-in',
        'Other',
    ];

    private const CONSENT_VERSION = 'candidate-privacy-v1';
    private const MAX_IMPORT_ROWS = 500;
    private const STAGES = [
        'received' => 'Received',
        'screening' => 'Screening',
        'shortlisted' => 'Shortlisted',
        'interview' => 'Interview',
        'assessment' => 'Assessment',
        'reference_check' => 'Reference Check',
        'offer' => 'Offer',
        'hired' => 'Hired',
        'rejected' => 'Rejected',
        'withdrawn' => 'Withdrawn',
    ];
    private const APPLICATION_STATUSES = [
        'received' => 'Not Processed',
        'in_progress' => 'In Progress',
        'hired' => 'Hired',
        'rejected' => 'Rejected',
        'withdrawn' => 'Withdrawn',
    ];

    public function getRegistrationPageData(User $user): array
    {
        $this->authorizeRecruitment($user);

        return [
            'positions' => Position::orderBy('name')->get(),
            'branches' => $this->accessibleBranches($user),
            'sources' => self::SOURCES,
        ];
    }

    public function getCandidateIndexData(User $user, array $filters = []): LengthAwarePaginator
    {
        $this->authorizeRecruitment($user);
        return $this->candidateIndexQuery($user, $filters)
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();
    }

    private function candidateIndexQuery(User $user, array $filters = []): Builder
    {
        $this->authorizeRecruitment($user);
        $applications = $this->matchingApplicationsQuery($user, $filters);
        $latestApplicationIds = (clone $applications)
            ->selectRaw('MAX(id)')
            ->groupBy('candidate_id');
        $candidateQuery = Candidate::query()
            ->whereIn('id', (clone $applications)->select('candidate_id')->distinct())
            ->withCount(['applications' => function ($query) use ($user) {
                $this->applyBranchScope($query, $user);
            }])
            ->with(['applications' => function ($query) use ($latestApplicationIds) {
                $query->whereIn('id', $latestApplicationIds)
                    ->with(['position', 'branch', 'hrPic']);
            }]);

        if (!empty($filters['q'])) {
            $search = '%' . trim($filters['q']) . '%';
            $candidateQuery->where(function (Builder $query) use ($search, $user) {
                $query->where('candidate_code', 'like', $search)
                    ->orWhere('full_name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhereHas('applications', function ($applicationQuery) use ($search, $user) {
                        $this->applyBranchScope($applicationQuery, $user);
                        $applicationQuery->where('position_applied', 'like', $search);
                    });
            });
        }

        $sort = $filters['sort'] ?? 'applied_at';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        if ($sort === 'candidate') {
            $candidateQuery->orderBy('full_name', $direction);
        } elseif ($sort === 'updated_at') {
            $candidateQuery->orderBy('updated_at', $direction);
        } else {
            $sortColumn = match ($sort) {
                'position' => 'position_applied',
                'branch' => 'branch_id',
                'stage' => 'stage',
                'last_activity_at' => 'last_activity_at',
                default => 'applied_at',
            };
            $latestValue = CandidateApplication::query()
                ->select($sortColumn)
                ->whereColumn('candidate_id', 'candidates.id')
                ->whereIn('id', (clone $applications)
                    ->selectRaw('MAX(id)')
                    ->groupBy('candidate_id'))
                ->limit(1);
            $candidateQuery->orderBy($latestValue, $direction);
        }
        $candidateQuery->orderBy('candidates.id');

        return $candidateQuery;
    }

    public function getCandidateIndexFilterData(User $user): array
    {
        $this->authorizeRecruitment($user);
        $applications = CandidateApplication::query();
        $this->applyBranchScope($applications, $user);

        $picQuery = User::query()->whereIn('id', (clone $applications)
            ->whereNotNull('hr_pic_id')
            ->select('hr_pic_id')
            ->distinct());

        return [
            'branches' => $this->accessibleBranches($user),
            'positions' => (clone $applications)->whereNotNull('position_applied')
                ->select('position_applied')->distinct()->orderBy('position_applied')->pluck('position_applied'),
            'sources' => (clone $applications)->whereNotNull('source')
                ->select('source')->distinct()->orderBy('source')->pluck('source'),
            'hrPics' => $picQuery->orderBy('name')->get(['id', 'name']),
            'assignableHrPics' => User::query()
                ->whereHas('roles', function ($query) {
                    $query->whereIn('roles.id', [1, 3])->orWhere('roles.name', 'admin');
                })
                ->orderBy('name')->get(['id', 'name']),
            'stages' => self::STAGES,
            'statuses' => self::APPLICATION_STATUSES,
        ];
    }

    public function exportCandidateIndex(User $user, array $filters = []): StreamedResponse
    {
        $this->authorizeRecruitment($user);
        $query = $this->candidateIndexQuery($user, $filters);

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Candidate Code',
                'Candidate Name',
                'Email',
                'Phone',
                'Position Applied',
                'Branch',
                'Source',
                'Application Status',
                'Current Stage',
                'HR PIC',
                'Last Activity',
                'Updated At',
                'Applications',
            ], ',', '"', '');

            $query->chunk(500, function (Collection $candidates) use ($output) {
                foreach ($candidates as $candidate) {
                    $application = $candidate->applications->first();
                    fputcsv($output, array_map($this->safeCsvValue(...), [
                        $candidate->candidate_code,
                        $candidate->full_name,
                        $candidate->email,
                        $candidate->phone,
                        $application?->position_applied ?: $application?->position?->name,
                        $application?->branch?->name,
                        $application?->source,
                        self::APPLICATION_STATUSES[$application?->status ?? ''] ?? $application?->status,
                        self::STAGES[$application?->stage ?? ''] ?? $application?->stage,
                        $application?->hrPic?->name,
                        $application?->last_activity_at?->format('Y-m-d H:i:s'),
                        $candidate->updated_at?->format('Y-m-d H:i:s'),
                        $candidate->applications_count,
                    ]), ',', '"', '');
                }
            });
            fclose($output);
        }, 'candidate-database-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function getCandidateDetails(Candidate $candidate, User $user): Candidate
    {
        $this->authorizeRecruitment($user);
        $branchScopeId = $this->branchScopeId($user);

        if ($branchScopeId === null) {
            return $candidate->load([
                'applications' => function ($query) {
                    $query->orderByDesc('applied_at')->with(['position', 'branch', 'hrPic']);
                },
            ]);
        }

        $candidate->load([
            'applications' => function ($query) use ($branchScopeId) {
                $query->where('branch_id', $branchScopeId)
                    ->orderByDesc('applied_at')
                    ->with(['position', 'branch', 'hrPic']);
            },
        ]);

        if ($candidate->applications->isEmpty()) {
            abort(404);
        }

        return $candidate;
    }

    public function updateCandidate(Candidate $candidate, array $data, User $user): Candidate
    {
        $this->authorizeRecruitment($user);
        $this->assertCandidateAccess($candidate, $user);
        $candidate->update($data);

        return $candidate->refresh();
    }

    public function updateCandidateApplication(
        Candidate $candidate,
        CandidateApplication $application,
        array $data,
        User $user
    ): CandidateApplication {
        $this->authorizeRecruitment($user);
        if ($application->candidate_id !== $candidate->id) {
            abort(404);
        }
        $this->assertBranchAccess($application->branch, $user);

        if (!in_array($data['stage'], array_keys(self::STAGES), true)) {
            throw new \InvalidArgumentException('Unsupported recruitment stage.');
        }

        if (!empty($data['hr_pic_id'])) {
            $isAssignable = User::query()
                ->whereKey($data['hr_pic_id'])
                ->whereHas('roles', function ($query) {
                    $query->whereIn('roles.id', [1, 3])->orWhere('roles.name', 'admin');
                })
                ->exists();
            if (!$isAssignable) {
                throw new AuthorizationException('The selected user cannot be assigned as an HR PIC.');
            }
        }

        $status = match ($data['stage']) {
            'received' => 'received',
            'hired', 'rejected', 'withdrawn' => $data['stage'],
            default => 'in_progress',
        };
        $application->update([
            'stage' => $data['stage'],
            'status' => $status,
            'hr_pic_id' => $data['hr_pic_id'] ?? null,
            'last_activity_at' => now(),
        ]);

        return $application->refresh()->load(['position', 'branch', 'hrPic']);
    }

    public function getSelfRegistrationPageData(): array
    {
        return [
            'positions' => Position::orderBy('name')->get(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    public function getSources(): array
    {
        return self::SOURCES;
    }

    public function registerCandidate(
        array $data,
        string $registrationMethod,
        ?User $registeredBy = null,
        ?UploadedFile $resume = null
    ): CandidateApplication {
        if (!in_array($registrationMethod, ['hr', 'import', 'self_registration'], true)) {
            throw new \InvalidArgumentException('Unsupported candidate registration method.');
        }

        if ($registeredBy) {
            $this->authorizeRecruitment($registeredBy);
        } elseif ($registrationMethod !== 'self_registration') {
            throw new AuthorizationException('An authenticated recruitment user is required.');
        }

        Validator::make($data, [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'current_city' => 'nullable|string|max:255',
            'position_applied' => $registrationMethod === 'import'
                ? 'nullable|string|max:255'
                : 'required|string|max:255',
            'position_id' => $registrationMethod === 'import'
                ? 'required|integer'
                : 'nullable|integer',
            'branch_id' => 'required|integer',
            'source' => $registrationMethod === 'self_registration'
                ? 'nullable'
                : 'required|in:' . implode(',', self::SOURCES),
            'source_detail' => 'nullable|string|max:255',
            'consent' => 'accepted',
        ])->validate();

        $position = $registrationMethod === 'import'
            ? Position::findOrFail($data['position_id'])
            : Position::whereRaw('LOWER(name) = ?', [Str::lower(trim($data['position_applied']))])->first();
        $positionName = $registrationMethod === 'import'
            ? $position->name
            : trim($data['position_applied']);
        $branch = Branch::where('is_active', true)->findOrFail($data['branch_id']);
        if ($registeredBy) {
            $this->assertBranchAccess($branch, $registeredBy);
        }

        $email = Str::lower(trim($data['email']));
        $candidate = $this->findCandidateByEmail($email);
        if ($candidate && $this->hasApplicationByPosition($candidate, $positionName, $position?->id, $branch->id)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'This candidate is already registered for the selected position and branch.',
            ]);
        }

        $resumePath = null;
        if ($resume) {
            $resumePath = $resume->store('recruitment/cv', 'local');
            if (!$resumePath) {
                throw new RuntimeException('Candidate CV could not be stored.');
            }
        }

        try {
            return DB::transaction(function () use (
                $data,
                $email,
                $position,
                $positionName,
                $branch,
                $registrationMethod,
                $registeredBy,
                $resumePath
            ) {
                $candidate = $this->findCandidateByEmail($email);
                if (!$candidate) {
                    $candidate = Candidate::create([
                        'full_name' => $data['full_name'],
                        'email' => $email,
                        'phone' => $data['phone'],
                        'current_city' => $data['current_city'] ?? null,
                    ]);
                    $candidate->candidate_code = sprintf('CAN-%s-%05d', now()->format('Y'), $candidate->id);
                    $candidate->save();
                }

                if ($this->hasApplicationByPosition($candidate, $positionName, $position?->id, $branch->id)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'email' => 'This candidate is already registered for the selected position and branch.',
                    ]);
                }

                return CandidateApplication::create([
                    'candidate_id' => $candidate->id,
                    'position_id' => $position?->id,
                    'position_applied' => $positionName,
                    'branch_id' => $branch->id,
                    'source' => $registrationMethod === 'self_registration'
                        ? 'Career Page'
                        : $data['source'],
                    'source_detail' => $data['source_detail'] ?? null,
                    'resume_path' => $resumePath,
                    'consent_at' => now(),
                    'consent_version' => self::CONSENT_VERSION,
                    'registration_method' => $registrationMethod,
                    'registered_by' => $registeredBy?->id,
                    'hr_pic_id' => $registeredBy?->id,
                    'status' => 'received',
                    'stage' => 'received',
                    'applied_at' => now(),
                    'last_activity_at' => now(),
                ]);
            });
        } catch (Throwable $exception) {
            if ($resumePath) {
                Storage::disk('local')->delete($resumePath);
            }

            throw $exception;
        }
    }

    public function previewImport(UploadedFile $file, User $user): array
    {
        $this->authorizeRecruitment($user);

        $rows = $this->readImportRows($file);
        if (count($rows) > self::MAX_IMPORT_ROWS) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => 'The import file can contain a maximum of ' . self::MAX_IMPORT_ROWS . ' candidate rows.',
            ]);
        }

        $assessment = $this->validateImportRows($rows, $user);
        if ($assessment['total'] === 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => 'The file does not contain any candidate data.',
            ]);
        }

        $token = null;
        if ($assessment['valid_count'] > 0) {
            $token = (string) Str::uuid();
            $extension = strtolower($file->getClientOriginalExtension() ?: 'csv');
            $path = $file->storeAs('recruitment/imports', $token . '.' . $extension, 'local');

            if (!$path) {
                throw new RuntimeException('Candidate import file could not be stored.');
            }
        }

        return [
            'preview' => $assessment,
            'token' => $token,
        ];
    }

    public function commitImport(string $token, User $user): array
    {
        $this->authorizeRecruitment($user);
        if (!Str::isUuid($token)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'import_token' => 'The import preview session is invalid.',
            ]);
        }

        $matches = glob(Storage::disk('local')->path('recruitment/imports/' . $token . '.*'));
        if (!$matches) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'import_token' => 'The preview file was not found or the import session has expired.',
            ]);
        }

        $path = $matches[0];
        $rows = $this->readImportRows($path);
        if (count($rows) > self::MAX_IMPORT_ROWS) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => 'The import file can contain a maximum of ' . self::MAX_IMPORT_ROWS . ' candidate rows.',
            ]);
        }

        $assessment = $this->validateImportRows($rows, $user);
        $validRows = array_values(array_filter(
            $assessment['rows'],
            fn (array $row) => $row['valid']
        ));

        if ($validRows === []) {
            $this->discardImportPreview($token);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => 'There are no valid rows to import. Please review the file data.',
            ]);
        }

        DB::transaction(function () use ($validRows, $user) {
            foreach ($validRows as $row) {
                $this->registerCandidate($row['data'], 'import', $user);
            }
        });

        $this->discardImportPreview($token);

        return [
            'imported_count' => count($validRows),
            'skipped_count' => $assessment['total'] - count($validRows),
        ];
    }

    public function discardImportPreview(?string $token): void
    {
        if (!$token || !Str::isUuid($token)) {
            return;
        }

        foreach (glob(Storage::disk('local')->path('recruitment/imports/' . $token . '.*')) ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function getImportTemplateHeadings(): array
    {
        return CandidateImport::templateHeadings();
    }

    public function getResumePath(
        Candidate $candidate,
        CandidateApplication $application,
        User $user
    ): string {
        $this->authorizeRecruitment($user);
        if ($application->candidate_id !== $candidate->id) {
            abort(404);
        }

        $this->assertBranchAccess($application->branch, $user);
        if (!$application->resume_path || !Storage::disk('local')->exists($application->resume_path)) {
            abort(404);
        }

        return $application->resume_path;
    }

    private function readImportRows($file): array
    {
        $sheets = Excel::toArray(new CandidateImport(), $file);

        return $sheets[0] ?? [];
    }

    private function validateImportRows(array $rows, User $user): array
    {
        $positions = Position::all()->keyBy(fn (Position $position) => Str::lower($position->name));
        $branchQuery = Branch::where('is_active', true);
        $branchScopeId = $this->branchScopeId($user);
        if ($branchScopeId !== null) {
            $branchQuery->whereKey($branchScopeId);
        }
        $branches = $branchQuery->get()->keyBy(fn (Branch $branch) => Str::lower($branch->name));
        $seenApplications = [];
        $result = [];
        $total = 0;

        foreach ($rows as $index => $row) {
            $data = $this->normalizeImportRow($row);
            if (count(array_filter($data, fn ($value) => $value !== null && $value !== '')) === 0) {
                continue;
            }

            $total++;
            $line = $index + 2;
            $validator = Validator::make($data, [
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone_number' => 'required|string|max:50',
                'current_city' => 'nullable|string|max:255',
                'position_applied' => 'required|string|max:255',
                'branch_preference' => 'required|string|max:255',
                'application_source' => 'required|in:' . implode(',', self::SOURCES),
                'source_detail' => 'nullable|string|max:255',
                'consent' => 'required|in:yes',
            ]);

            $errors = $validator->fails() ? $validator->errors()->all() : [];
            $position = $positions->get(Str::lower((string) ($data['position_applied'] ?? '')));
            $branch = $branches->get(Str::lower((string) ($data['branch_preference'] ?? '')));
            if (!$position) {
                $errors[] = 'The position was not found in the Job Position records.';
            }
            if (!$branch) {
                $errors[] = 'The branch was not found or is inactive.';
            }

            if ($errors === []) {
                $email = Str::lower($data['email']);
                $candidate = $this->findCandidateByEmail($email);
                $applicationKey = $email . '|' . $position->id . '|' . $branch->id;

                if (isset($seenApplications[$applicationKey])) {
                    $errors[] = 'Duplikat lamaran dalam file import.';
                } elseif ($candidate && $this->hasApplication($candidate, $position->id, $branch->id)) {
                    $errors[] = 'An application for this position and branch is already registered.';
                } else {
                    $seenApplications[$applicationKey] = true;
                }
            }

            $data['email'] = isset($data['email']) ? Str::lower($data['email']) : null;
            $data['source'] = $data['application_source'] ?? null;
            $data['source_detail'] = $data['source_detail'] ?? null;
            $data['phone'] = $data['phone_number'] ?? null;
            $data['current_city'] = $data['current_city'] ?? null;
            $data['position_id'] = $position?->id;
            $data['branch_id'] = $branch?->id;

            $result[] = [
                'line' => $line,
                'valid' => $errors === [],
                'errors' => $errors,
                'data' => $data,
                'position' => $position,
                'branch' => $branch,
            ];
        }

        return [
            'rows' => $result,
            'total' => $total,
            'valid_count' => count(array_filter($result, fn (array $row) => $row['valid'])),
            'invalid_count' => count(array_filter($result, fn (array $row) => !$row['valid'])),
        ];
    }

    private function normalizeImportRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalized[$key] = is_scalar($value) ? trim((string) $value) : null;
        }

        $normalized['email'] = isset($normalized['email'])
            ? Str::lower($normalized['email'])
            : null;
        $normalized['application_source'] = $this->normalizeSource($normalized['application_source'] ?? null);
        $normalized['consent'] = Str::lower($normalized['consent'] ?? '');

        return $normalized;
    }

    private function normalizeSource(?string $source): ?string
    {
        foreach (self::SOURCES as $allowedSource) {
            if (Str::lower($allowedSource) === Str::lower(trim((string) $source))) {
                return $allowedSource;
            }
        }

        return $source;
    }

    private function findCandidateByEmail(string $email): ?Candidate
    {
        return Candidate::whereRaw('LOWER(email) = ?', [Str::lower($email)])->first();
    }

    private function matchingApplicationsQuery(User $user, array $filters): Builder
    {
        $query = CandidateApplication::query();
        $this->applyBranchScope($query, $user);

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
        if (!empty($filters['position'])) {
            $position = $filters['position'];
            $query->where(function (Builder $positionQuery) use ($position) {
                $positionQuery->where('position_applied', $position)
                    ->orWhereHas('position', fn ($related) => $related->where('name', $position));
            });
        }
        foreach (['status', 'stage', 'source', 'hr_pic_id'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        if (!empty($filters['applied_from'])) {
            $query->whereDate('applied_at', '>=', $filters['applied_from']);
        }
        if (!empty($filters['applied_to'])) {
            $query->whereDate('applied_at', '<=', $filters['applied_to']);
        }
        if (!empty($filters['inactive_days'])) {
            $inactiveBefore = now()->subDays((int) $filters['inactive_days']);
            $query->where(function (Builder $activityQuery) use ($inactiveBefore) {
                $activityQuery->whereNull('last_activity_at')
                    ->orWhere('last_activity_at', '<=', $inactiveBefore);
            });
        }

        return $query;
    }

    private function applyBranchScope(Builder|Relation $query, User $user): void
    {
        $branchScopeId = $this->branchScopeId($user);
        if ($branchScopeId !== null) {
            $query->where('branch_id', $branchScopeId);
        }
    }

    private function assertCandidateAccess(Candidate $candidate, User $user): void
    {
        $applications = $candidate->applications();
        $this->applyBranchScope($applications->getQuery(), $user);
        if (!$applications->exists()) {
            abort(404);
        }
    }

    private function safeCsvValue(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        return preg_match('/^[\s]*[=+\-@]/', $value) ? "'" . $value : $value;
    }

    private function hasApplication(Candidate $candidate, int $positionId, int $branchId): bool
    {
        return $candidate->applications()
            ->where('position_id', $positionId)
            ->where('branch_id', $branchId)
            ->where('status', 'received')
            ->exists();
    }

    private function hasApplicationByPosition(
        Candidate $candidate,
        string $positionName,
        ?int $positionId,
        int $branchId
    ): bool {
        return $candidate->applications()
            ->where('branch_id', $branchId)
            ->where('status', 'received')
            ->where(function ($query) use ($positionName, $positionId) {
                $query->whereRaw('LOWER(position_applied) = ?', [Str::lower($positionName)]);
                if ($positionId !== null) {
                    $query->orWhere('position_id', $positionId);
                }
            })
            ->exists();
    }

    private function authorizeRecruitment(User $user): void
    {
        if (
            !$user->hasRole('admin')
            && !$user->roles->contains('id', 1)
            && !$user->roles->contains('id', 3)
        ) {
            throw new AuthorizationException('Recruitment access is not allowed.');
        }
    }

    private function branchScopeId(User $user): ?int
    {
        if ($user->hasRole('admin') || $user->roles->contains('id', 1)) {
            return null;
        }

        $branchId = $user->employee?->employment?->branch_id;
        if (!$branchId) {
            throw new AuthorizationException('The user does not have an assigned branch.');
        }

        return (int) $branchId;
    }

    private function accessibleBranches(User $user): Collection
    {
        $branches = Branch::where('is_active', true);
        $branchScopeId = $this->branchScopeId($user);

        if ($branchScopeId !== null) {
            $branches->whereKey($branchScopeId);
        }

        return $branches->orderBy('name')->get();
    }

    private function assertBranchAccess(Branch $branch, User $user): void
    {
        $branchScopeId = $this->branchScopeId($user);
        if ($branchScopeId !== null && $branchScopeId !== $branch->id) {
            throw new AuthorizationException('The user cannot access candidates from this branch.');
        }
    }
}
