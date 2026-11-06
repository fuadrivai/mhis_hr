<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitorVisitRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\VisitorApproval;
use App\Models\VisitorCategory;
use App\Models\VisitorPurpose;
use App\Models\VisitorVisit;
use App\Models\VisitorVisitLog;
use App\Services\VisitorManagementService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VisitorManagementController extends Controller
{
    public function __construct(private VisitorManagementService $visitorService)
    {
    }

    public function dashboard()
    {
        $query = $this->visibleVisits();
        $today = now()->toDateString();

        return view('visitor.dashboard', [
            'title' => 'Visitor Management Dashboard',
            'todayCount' => (clone $query)->whereDate('visit_date', $today)->count(),
            'insideCount' => (clone $query)->where('status', 'checked_in')->count(),
            'expectedCount' => (clone $query)->where('status', 'expected')->whereDate('visit_date', $today)->count(),
            'pendingCount' => $this->pendingApprovals()->count(),
            'currentlyInside' => (clone $query)->where('status', 'checked_in')
                ->with(['visitor', 'branch', 'host.user', 'purpose'])
                ->latest('check_in_at')->limit(10)->get(),
            'upcomingVisits' => (clone $query)->where('status', 'expected')
                ->whereDate('visit_date', '>=', $today)
                ->with(['visitor', 'branch', 'host.user', 'purpose'])
                ->orderBy('visit_date')->orderBy('expected_arrival_at')->limit(10)->get(),
            'overstayVisits' => (clone $query)->where('status', 'checked_in')
                ->whereNotNull('expected_departure_at')
                ->where('expected_departure_at', '<', now())
                ->with(['visitor', 'branch', 'host.user'])
                ->orderBy('expected_departure_at')->limit(10)->get(),
            'recentActivity' => VisitorVisitLog::whereIn('visitor_visit_id', (clone $query)->select('id'))
                ->with(['visit.visitor', 'user'])
                ->latest('created_at')->limit(10)->get(),
            'canUseDesk' => $this->canUseDesk(),
        ]);
    }

    public function index(Request $request)
    {
        if ($request->boolean('clear_search')) {
            $request->session()->forget('visitor_search_term');
        }
        $term = (string) $request->session()->get('visitor_search_term', '');
        $visits = $this->visibleVisits()
            ->with(['visitor', 'branch', 'host.user', 'category', 'purpose'])
            ->when($term !== '', function (Builder $query) use ($term) {
                $tokenHash = hash('sha256', $term);
                $query->where(function (Builder $searchQuery) use ($term, $tokenHash) {
                    $searchQuery->where('visit_code', $term)
                        ->orWhere('qr_token_hash', $tokenHash)
                        ->orWhere('badge_number', $term)
                        ->orWhereHas('visitor', function (Builder $visitorQuery) use ($term) {
                            $visitorQuery->where('full_name', 'like', "%{$term}%")
                                ->orWhere('phone', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%");
                        });
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('from'), fn (Builder $query) => $query->whereDate('visit_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn (Builder $query) => $query->whereDate('visit_date', '<=', $request->input('to')))
            ->latest('visit_date')->paginate(20)->withQueryString();

        return view('visitor.index', [
            'title' => 'Visitor Visits',
            'visits' => $visits,
            'canUseDesk' => $this->canUseDesk(),
            'statuses' => $this->statuses(),
            'searchTerm' => $term,
        ]);
    }

    public function searchList(Request $request)
    {
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:255']]);
        $request->session()->put('visitor_search_term', trim((string) ($validated['search'] ?? '')));

        return redirect()->route('visitor.index');
    }

    public function create()
    {
        $this->employee();

        return view('visitor.create', ['title' => 'Create Visitor Invitation'] + $this->formData());
    }

    public function store(StoreVisitorVisitRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('visitor-photos');
        }

        $created = $this->visitorService->createVisit($request->user(), $data, 'employee_invitation');
        $qrSvg = $this->qrSvg($created['qr_token']);

        return redirect()->route('visitor.index')
            ->with('success', 'Visitor invitation created.')
            ->with('visitor_qr_token', $created['qr_token'])
            ->with('visitor_qr_image', $this->qrDataUri($qrSvg))
            ->with('visitor_visit_code', $created['visit']->visit_code);
    }

    public function approvals()
    {
        $query = VisitorApproval::query()
            ->where('status', 'pending')
            ->with(['visit.visitor', 'visit.branch', 'visit.host.user', 'visit.category', 'visit.purpose']);

        if ($this->isAdministrator()) {
            $query->whereHas('visit', fn (Builder $visitQuery) => $visitQuery->where('status', 'pending_confirmation'));
        } elseif ($this->isHrd() || $this->user()->hasRole('ga')) {
            $query->whereHas('visit', function (Builder $visitQuery) {
                $visitQuery->where('status', 'pending_confirmation')
                    ->where('branch_id', $this->branchId());
            });
        } else {
            $query->where('approver_employee_id', $this->employee()->id)
                ->whereHas('visit', fn (Builder $visitQuery) => $visitQuery->where('status', 'pending_confirmation'));
        }

        return view('visitor.approvals', [
            'title' => 'Visitor Approvals',
            'approvals' => $query->latest()->paginate(20),
        ]);
    }

    public function processApproval(Request $request, VisitorVisit $visit)
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->authorizeApproval($visit);

        $visit = $this->visitorService->processApproval(
            $request->user(),
            $visit,
            $validated['decision'],
            $validated['notes'] ?? null
        );
        return back()->with('success', 'Approval decision recorded.');
    }

    public function securityDesk()
    {
        abort_unless($this->canUseDesk(), 403);

        return view('visitor.security-desk', [
            'title' => 'Security Visitor Desk',
            'branches' => $this->availableBranches(),
            'categories' => VisitorCategory::where('is_active', true)->orderBy('name')->get(),
            'purposes' => VisitorPurpose::where('is_active', true)->orderBy('name')->get(),
            'hosts' => Employee::with(['user', 'employment'])
                ->where('is_active', true)
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->get(),
            'visitsInside' => $this->visibleVisits()->where('status', 'checked_in')
                ->with(['visitor', 'branch', 'host.user', 'purpose'])->latest('check_in_at')->limit(20)->get(),
            'expectedVisits' => $this->visibleVisits()->where('status', 'expected')
                ->whereDate('visit_date', now()->toDateString())
                ->with(['visitor', 'branch', 'host.user', 'purpose'])->orderBy('expected_arrival_at')->limit(20)->get(),
            'canManageVisits' => $this->canManageVisits(),
        ]);
    }

    public function search(Request $request)
    {
        abort_unless($this->canUseDesk(), 403);
        $validated = $request->validate(['query' => ['required', 'string', 'max:255']]);
        $term = trim($validated['query']);
        $visits = $this->visibleVisits()->with(['visitor', 'branch', 'host.user', 'category', 'purpose'])
            ->where(function (Builder $builder) use ($term) {
            $builder->where('visit_code', $term)
                ->orWhere('qr_token_hash', hash('sha256', $term))
                ->orWhere('badge_number', $term)
                ->orWhereHas('visitor', function (Builder $visitorQuery) use ($term) {
                    $visitorQuery->where('full_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->latest('visit_date')->limit(20)->get();

        if ($visits->isEmpty()) {
            return response()->json(['message' => 'No matching visit was found.'], 404);
        }

        $results = $visits->map(fn (VisitorVisit $visit) => [
                'id' => $visit->id,
                'visit_code' => $visit->visit_code,
                'visitor' => $visit->visitor->full_name,
                'company' => $visit->visitor->company,
                'visitor_notes' => $visit->visitor_notes,
                'phone' => $visit->visitor->phone,
                'category' => $visit->category->name,
                'purpose' => $visit->purpose->name,
                'branch' => $visit->branch->name,
                'host' => $visit->host->user?->name ?? '—',
                'date' => $visit->visit_date->format('d M Y'),
                'arrival' => $visit->expected_arrival_at?->format('H:i'),
                'departure' => $visit->expected_departure_at?->format('H:i'),
                'status' => $visit->status,
                'check_in_at' => $visit->check_in_at?->format('d M Y H:i'),
                'badge_number' => $visit->badge_number,
                'can_check_in' => $visit->status === 'expected' && $visit->visit_date->isToday(),
                'can_check_out' => $visit->status === 'checked_in',
            ])->values();

        return response()->json(['visits' => $results]);
    }

    public function storeWalkIn(Request $request)
    {
        abort_unless($this->canUseDesk(), 403);
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40', 'required_without:email'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'company' => ['nullable', 'string', 'max:255'],
            'visitor_category_id' => ['required', Rule::exists('visitor_categories', 'id')->where('is_active', true)],
            'visitor_purpose_id' => ['required', Rule::exists('visitor_purposes', 'id')->where('is_active', true)],
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('is_active', true)],
            'host_employee_id' => ['required', Rule::exists('employees', 'id')->where('is_active', true)],
            'expected_departure_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'visitor_notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        if (\Carbon\Carbon::parse($validated['expected_departure_at'])->toDateString() !== now()->toDateString()) {
            return back()->withErrors(['expected_departure_at' => 'Walk-in visitors must depart on the registration date.'])->withInput();
        }
        $category = VisitorCategory::findOrFail($validated['visitor_category_id']);
        if ($category->requires_additional_info && empty($validated['visitor_notes']) && empty($validated['notes'])) {
            return back()->withErrors(['visitor_notes' => 'Additional information is required for this visitor category.'])->withInput();
        }
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('visitor-photos');
        }
        $validated['visit_date'] = now()->toDateString();
        $validated['expected_arrival_at'] = now();

        $created = $this->visitorService->createVisit($request->user(), $validated, 'walk_in');
        $qrSvg = $this->qrSvg($created['qr_token']);

        return redirect()->route('visitor.security-desk')
            ->with('success', $created['visit']->status === 'pending_confirmation'
                ? 'Walk-in registered and is waiting for host approval.'
                : 'Walk-in registered. Confirm identity before checking the visitor in.')
            ->with('visitor_qr_token', $created['qr_token'])
            ->with('visitor_qr_image', $this->qrDataUri($qrSvg))
            ->with('visitor_visit_code', $created['visit']->visit_code);
    }

    public function checkIn(Request $request, VisitorVisit $visit)
    {
        abort_unless($this->canUseDesk(), 403);
        $this->authorizeBranch($visit);
        $validated = $request->validate([
            'identity_verified' => ['accepted'],
            'badge_number' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        if ($request->hasFile('photo')) {
            $validated['check_in_photo'] = $request->file('photo')->store('visitor-photos/check-in');
        }

        $this->visitorService->checkIn($request->user(), $visit, $validated);

        return back()->with('success', 'Visitor checked in.');
    }

    public function checkOut(Request $request, VisitorVisit $visit)
    {
        abort_unless($this->canUseDesk(), 403);
        $this->authorizeBranch($visit);
        $validated = $request->validate([
            'confirmed' => ['accepted'],
            'badge_return_status' => ['nullable', Rule::in(['returned', 'lost', 'not_applicable'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->visitorService->checkOut($request->user(), $visit, $validated);

        return back()->with('success', 'Visitor checked out.');
    }

    public function correctCheckOut(Request $request, VisitorVisit $visit)
    {
        abort_unless($this->canManageVisits(), 403);
        $this->authorizeBranch($visit);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        $this->visitorService->correctCheckOut($request->user(), $visit, $validated['reason']);

        return back()->with('success', 'Manual check-out correction recorded.');
    }

    public function cancel(Request $request, VisitorVisit $visit)
    {
        abort_unless($visit->registered_by === $request->user()->id || $this->isAdministrator(), 403);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $this->visitorService->cancelVisit($request->user(), $visit, $validated['reason']);

        return back()->with('success', 'Visit cancelled.');
    }

    public function history(Request $request)
    {
        $visits = $this->visibleVisits()
            ->with(['visitor', 'branch', 'host.user', 'category', 'purpose'])
            ->when($request->filled('from'), fn (Builder $query) => $query->whereDate('visit_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn (Builder $query) => $query->whereDate('visit_date', '<=', $request->input('to')))
            ->whereIn('status', ['completed', 'rejected', 'cancelled', 'no_show'])
            ->latest('visit_date')->paginate(30)->withQueryString();

        return view('visitor.history', ['title' => 'Visitor History', 'visits' => $visits]);
    }

    public function reports(Request $request)
    {
        abort_unless($this->canManageVisits(), 403);
        $query = $this->visibleVisits()
            ->with(['visitor', 'branch', 'host.user', 'category', 'purpose'])
            ->when($request->filled('from'), fn (Builder $builder) => $builder->whereDate('visit_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn (Builder $builder) => $builder->whereDate('visit_date', '<=', $request->input('to')));

        return view('visitor.reports', [
            'title' => 'Visitor Reports',
            'visits' => (clone $query)->latest('visit_date')->paginate(30)->withQueryString(),
            'totalCount' => (clone $query)->count(),
            'insideCount' => (clone $query)->where('status', 'checked_in')->count(),
            'completedCount' => (clone $query)->where('status', 'completed')->count(),
            'queryParams' => $request->only(['from', 'to']),
        ]);
    }

    public function export(Request $request)
    {
        abort_unless($this->canManageVisits(), 403);
        $visits = $this->visibleVisits()
            ->with(['visitor', 'branch', 'host.user', 'category', 'purpose'])
            ->when($request->filled('from'), fn (Builder $builder) => $builder->whereDate('visit_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn (Builder $builder) => $builder->whereDate('visit_date', '<=', $request->input('to')))
            ->orderByDesc('visit_date')->get();

        return response()->streamDownload(function () use ($visits) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Visit Code', 'Visitor', 'Company', 'Branch', 'Host', 'Category', 'Purpose', 'Visit Date', 'Check-in', 'Check-out', 'Status']);
            foreach ($visits as $visit) {
                fputcsv($output, [
                    $this->safeCsvValue($visit->visit_code),
                    $this->safeCsvValue($visit->visitor->full_name),
                    $this->safeCsvValue($visit->visitor->company),
                    $this->safeCsvValue($visit->branch->name),
                    $this->safeCsvValue($visit->host->user?->name),
                    $this->safeCsvValue($visit->category->name),
                    $this->safeCsvValue($visit->purpose->name),
                    $this->safeCsvValue($visit->visit_date->toDateString()),
                    $this->safeCsvValue($visit->check_in_at?->toDateTimeString()),
                    $this->safeCsvValue($visit->check_out_at?->toDateTimeString()),
                    $this->safeCsvValue($visit->status),
                ]);
            }
            fclose($output);
        }, 'visitor-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function settings()
    {
        abort_unless($this->isAdministrator(), 403);

        return view('visitor.settings', [
            'title' => 'Visitor Settings',
            'categories' => VisitorCategory::withCount('visits')->orderBy('name')->get(),
            'purposes' => VisitorPurpose::withCount('visits')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        abort_unless($this->isAdministrator(), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:visitor_categories,name'],
            'requires_approval' => ['sometimes', 'boolean'],
            'requires_additional_info' => ['sometimes', 'boolean'],
        ]);
        VisitorCategory::create([
            'name' => $data['name'],
            'requires_approval' => $request->boolean('requires_approval'),
            'requires_additional_info' => $request->boolean('requires_additional_info'),
        ]);

        return back()->with('success', 'Visitor category created.');
    }

    public function updateCategory(Request $request, VisitorCategory $category)
    {
        abort_unless($this->isAdministrator(), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('visitor_categories', 'name')->ignore($category->id)],
            'is_active' => ['required', 'boolean'],
            'requires_approval' => ['required', 'boolean'],
            'requires_additional_info' => ['required', 'boolean'],
        ]);
        $category->update($data);

        return back()->with('success', 'Visitor category updated.');
    }

    public function storePurpose(Request $request)
    {
        abort_unless($this->isAdministrator(), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:visitor_purposes,name'],
            'requires_approval' => ['sometimes', 'boolean'],
        ]);
        VisitorPurpose::create([
            'name' => $data['name'],
            'requires_approval' => $request->boolean('requires_approval'),
        ]);

        return back()->with('success', 'Visit purpose created.');
    }

    public function updatePurpose(Request $request, VisitorPurpose $purpose)
    {
        abort_unless($this->isAdministrator(), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('visitor_purposes', 'name')->ignore($purpose->id)],
            'is_active' => ['required', 'boolean'],
            'requires_approval' => ['required', 'boolean'],
        ]);
        $purpose->update($data);

        return back()->with('success', 'Visit purpose updated.');
    }

    private function formData(): array
    {
        $employee = $this->employee();

        return [
            'branches' => $this->availableBranches(),
            'categories' => VisitorCategory::where('is_active', true)->orderBy('name')->get(),
            'purposes' => VisitorPurpose::where('is_active', true)->orderBy('name')->get(),
            'hosts' => Employee::with(['user', 'employment'])
                ->where('is_active', true)
                ->whereNotNull('user_id')
                ->orderBy('id')->get(),
            'defaultBranchId' => $this->isAdministrator()
                ? ($employee->employment?->branch_id ?? $this->availableBranches()->first()?->id)
                : $this->branchId(),
            'defaultHostId' => $employee->id,
        ];
    }

    private function availableBranches()
    {
        return Branch::where('is_active', true)
            ->when(!$this->isAdministrator(), fn (Builder $query) => $query->whereKey($this->branchId()))
            ->orderBy('name')->get();
    }

    private function authorizeApproval(VisitorVisit $visit): void
    {
        if ($this->isAdministrator()) {
            return;
        }
        $employee = $this->employee();
        $isAssignedHost = (int) $visit->host_employee_id === (int) $employee->id;
        $isBranchApprover = ($this->isHrd() || $this->user()->hasRole('ga'))
            && (int) $visit->branch_id === $this->branchId();
        abort_unless(($isAssignedHost || $isBranchApprover) && (int) $visit->registered_by !== (int) $this->user()->id, 403);
    }

    private function authorizeBranch(VisitorVisit $visit): void
    {
        if (!$this->isAdministrator()) {
            abort_unless((int) $visit->branch_id === $this->branchId(), 403);
        }
    }

    private function canUseDesk(): bool
    {
        return $this->isAdministrator()
            || $this->isHrd()
            || $this->user()->hasRole('security')
            || $this->user()->hasRole('ga');
    }

    private function canManageVisits(): bool
    {
        return $this->isAdministrator()
            || $this->isHrd()
            || $this->user()->hasRole('ga')
            || $this->user()->hasRole('visitor-manager');
    }

    private function isAdministrator(): bool
    {
        return $this->user()->hasRole('admin') || $this->user()->roles->contains('id', 1);
    }

    private function isHrd(): bool
    {
        return (bool) $this->user()->employee?->is_hrd
            || $this->user()->hasRole('hrd')
            || $this->user()->hasRole('visitor-manager');
    }

    private function branchId(): int
    {
        $branchId = $this->user()->employee?->employment?->branch_id;
        abort_unless($branchId, 403, 'A branch assignment is required to access visitor records.');

        return (int) $branchId;
    }

    private function employee(): Employee
    {
        $employee = $this->user()->employee;
        abort_unless($employee, 403, 'An employee profile is required.');

        return $employee;
    }

    private function user(): \App\Models\User
    {
        return auth()->user();
    }

    private function visibleVisits(): Builder
    {
        $query = VisitorVisit::query();
        if ($this->isAdministrator()) {
            return $query;
        }

        $branchId = $this->branchId();
        if ($this->canManageVisits() || $this->canUseDesk()) {
            return $query->where('branch_id', $branchId);
        }

        return $query->where('registered_by', $this->user()->id);
    }

    private function pendingApprovals(): Builder
    {
        $query = VisitorApproval::query()->where('status', 'pending');
        if ($this->isAdministrator()) {
            return $query;
        }
        if ($this->isHrd() || $this->user()->hasRole('ga')) {
            return $query->whereHas('visit', fn (Builder $visitQuery) => $visitQuery->where('branch_id', $this->branchId()));
        }

        return $query->where('approver_employee_id', $this->employee()->id);
    }

    private function statuses(): array
    {
        return [
            'pending_confirmation' => 'Pending Confirmation',
            'expected' => 'Expected',
            'rejected' => 'Rejected',
            'checked_in' => 'Checked In',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ];
    }

    private function qrSvg(string $token): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'scale' => 5,
        ]);
        return (new QRCode($options))->render($token);
    }

    private function qrDataUri(string $svg): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function safeCsvValue(?string $value): ?string
    {
        return $value !== null && preg_match('/^[\t\r\n ]*[=+\-@]/', $value)
            ? "'" . $value
            : $value;
    }
}
