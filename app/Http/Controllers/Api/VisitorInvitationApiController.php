<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorVisitRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\VisitorCategory;
use App\Models\VisitorPurpose;
use App\Models\VisitorVisit;
use App\Services\VisitorManagementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VisitorInvitationApiController extends Controller
{
    public function __construct(private VisitorManagementService $visitorService)
    {
    }

    public function formOptions(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $this->employee($request);
        $isAdmin = $user->hasRole('admin') || $user->roles()->where('roles.id', 1)->exists();
        $branchId = $employee->employment?->branch_id;
        if (!$isAdmin && !$branchId) {
            abort(403, 'A branch assignment is required to create invitations.');
        }

        return response()->json(['data' => [
            'branches' => Branch::where('is_active', true)
                ->when(!$isAdmin, fn (Builder $query) => $query->whereKey($branchId))
                ->orderBy('name')->get(['id', 'name']),
            'categories' => VisitorCategory::where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'requires_approval', 'requires_additional_info']),
            'purposes' => VisitorPurpose::where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'requires_approval']),
            'default_branch_id' => $branchId,
            'default_host_employee_id' => $employee->id,
        ]]);
    }

    public function hosts(Request $request): JsonResponse
    {
        $this->employee($request);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $hosts = Employee::query()->where('is_active', true)->whereNotNull('user_id')
            ->whereHas('user', function (Builder $query) use ($filters) {
                if (!empty($filters['search'])) {
                    $query->where('name', 'like', '%' . trim($filters['search']) . '%');
                }
            })->with('user:id,name')->orderBy('id')
            ->paginate($filters['per_page'] ?? 20)->withQueryString();
        $hosts->getCollection()->transform(fn (Employee $host) => [
            'id' => $host->id,
            'name' => $host->user->name,
        ]);

        return response()->json($hosts);
    }

    public function index(Request $request): JsonResponse
    {
        $employee = $this->employee($request);

        return $this->listVisits($request, VisitorVisit::query()->where('host_employee_id', $employee->id));
    }

    public function monitoring(Request $request): JsonResponse
    {
        $this->employee($request);

        return $this->listVisits($request, $this->ownedVisits($request));
    }

    private function listVisits(Request $request, Builder $query): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['pending_confirmation', 'expected', 'rejected', 'checked_in', 'completed', 'cancelled', 'no_show'])],
            'view' => ['nullable', Rule::in(['all', 'today', 'upcoming', 'history'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $query->with(['visitor', 'branch', 'host.user', 'category', 'purpose']);

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function (Builder $builder) use ($term) {
                $builder->where('visit_code', $term)
                    ->orWhereHas('visitor', function (Builder $visitor) use ($term) {
                        $visitor->where('full_name', 'like', '%' . $term . '%')
                            ->orWhere('phone', 'like', '%' . $term . '%')
                            ->orWhere('email', 'like', '%' . $term . '%');
                    });
            });
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['from'])) {
            $query->whereDate('visit_date', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('visit_date', '<=', $filters['to']);
        }
        switch ($filters['view'] ?? 'all') {
            case 'today':
                $query->whereDate('visit_date', today());
                break;
            case 'upcoming':
                $query->whereDate('visit_date', '>', today())
                    ->whereIn('status', ['expected', 'pending_confirmation']);
                break;
            case 'history':
                $query->whereIn('status', ['completed', 'rejected', 'cancelled', 'no_show']);
                break;
        }

        $visits = $query->orderByDesc('visit_date')->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 20)->withQueryString();
        $visits->getCollection()->transform(fn (VisitorVisit $visit) => $this->visitData($visit, $request->user()->id));

        return response()->json($visits);
    }

    public function store(StoreVisitorVisitRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('visitor-photos');
        }

        $created = $this->visitorService->createVisit($request->user(), $data, 'employee_invitation');
        $visit = $created['visit'];

        return response()->json([
            'message' => $visit->status === 'pending_confirmation'
                ? 'Visitor invitation submitted for host approval.'
                : 'Visitor invitation created.',
            'data' => [
                'id' => $visit->id,
                'visit_code' => $visit->visit_code,
                'status' => $visit->status,
                'visit_date' => $visit->visit_date->toDateString(),
                'expected_arrival_at' => $visit->expected_arrival_at?->toIso8601String(),
                'expected_departure_at' => $visit->expected_departure_at?->toIso8601String(),
                'branch_id' => $visit->branch_id,
                'host_employee_id' => $visit->host_employee_id,
            ],
        ], $visit->status === 'pending_confirmation' ? 202 : 201);
    }

    public function show(Request $request, int $visit): JsonResponse
    {
        $employee = $this->employee($request);
        $visit = VisitorVisit::query()
            ->where(function (Builder $query) use ($request, $employee) {
                $query->where('registered_by', $request->user()->id)
                    ->orWhere('host_employee_id', $employee->id);
            })
            ->with(['visitor', 'branch', 'host.user', 'category', 'purpose'])
            ->findOrFail($visit);

        return response()->json(['data' => $this->visitData($visit, $request->user()->id)]);
    }

    public function cancel(Request $request, int $visit): JsonResponse
    {
        $this->employee($request);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $visit = $this->ownedVisits($request)->findOrFail($visit);
        $visit = $this->visitorService->cancelVisit($request->user(), $visit, $validated['reason']);
        $visit->loadMissing('host.user');

        return response()->json(['message' => 'Visit cancelled.', 'data' => $this->visitData($visit, $request->user()->id)]);
    }

    private function employee(Request $request): Employee
    {
        $employee = $request->user()?->employee;
        abort_unless($employee, 403, 'An employee profile is required.');

        return $employee;
    }

    private function ownedVisits(Request $request): Builder
    {
        return VisitorVisit::query()->where('registered_by', $request->user()->id);
    }

    private function visitData(VisitorVisit $visit, int $userId): array
    {
        return [
            'id' => $visit->id,
            'visit_code' => $visit->visit_code,
            'registration_method' => $visit->registration_method,
            'status' => $visit->status,
            'visit_date' => $visit->visit_date->toDateString(),
            'expected_arrival_at' => $visit->expected_arrival_at?->toIso8601String(),
            'expected_departure_at' => $visit->expected_departure_at?->toIso8601String(),
            'check_in_at' => $visit->check_in_at?->toIso8601String(),
            'check_out_at' => $visit->check_out_at?->toIso8601String(),
            'notes' => $visit->notes,
            'visitor_notes' => $visit->visitor_notes,
            'cancellation_reason' => $visit->cancellation_reason,
            'can_cancel' => (int) $visit->registered_by === $userId
                && in_array($visit->status, ['expected', 'pending_confirmation'], true)
                && !$visit->visit_date->lt(today()),
            'visitor' => $visit->visitor ? [
                'id' => $visit->visitor->id,
                'full_name' => $visit->visitor->full_name,
                'phone' => $visit->visitor->phone,
                'email' => $visit->visitor->email,
                'company' => $visit->visitor->company,
            ] : null,
            'host' => $visit->host ? [
                'employee_id' => $visit->host->id,
                'name' => $visit->host->user?->name,
            ] : null,
            'branch' => $visit->branch ? ['id' => $visit->branch->id, 'name' => $visit->branch->name] : null,
            'category' => $visit->category ? ['id' => $visit->category->id, 'name' => $visit->category->name] : null,
            'purpose' => $visit->purpose ? ['id' => $visit->purpose->id, 'name' => $visit->purpose->name] : null,
        ];
    }
}
