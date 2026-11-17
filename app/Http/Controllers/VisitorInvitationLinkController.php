<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitorInvitationBatchRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use App\Models\VisitorCategory;
use App\Models\VisitorInvitationBatch;
use App\Models\VisitorInvitationLink;
use App\Models\VisitorPurpose;
use App\Models\VisitorVisit;
use App\Services\VisitorManagementService;
use App\Notifications\VisitorRegistrationLinkNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class VisitorInvitationLinkController extends Controller
{
    public function __construct(private VisitorManagementService $visitorService)
    {
    }

    public function index(Request $request)
    {
        $batches = VisitorInvitationBatch::query()
            ->where('created_by', $request->user()->id)
            ->with(['branch', 'host.user', 'category', 'purpose'])
            ->withCount('links')
            ->withCount([
                'links as submitted_links_count' => fn (Builder $query) => $query->whereNotNull('submitted_at'),
                'links as revoked_links_count' => fn (Builder $query) => $query->whereNotNull('revoked_at'),
            ])
            ->latest()
            ->paginate(20);

        return view('visitor.invitation-links.index', [
            'title' => 'Guest Registration Links',
            'batches' => $batches,
        ]);
    }

    public function create(Request $request)
    {
        $employee = $request->user()->employee;
        $isAdmin = $request->user()->hasRole('admin')
            || $request->user()->roles()->where('roles.id', 1)->exists();
        $branchId = $employee->employment?->branch_id;
        abort_unless($isAdmin || $branchId, 403, 'A branch assignment is required to create invitations.');

        return view('visitor.invitation-links.create', [
            'title' => 'Create Guest Registration Links',
            'branches' => Branch::where('is_active', true)
                ->when(!$isAdmin, fn (Builder $query) => $query->whereKey($branchId))
                ->orderBy('name')->get(),
            'hosts' => Employee::with('user')->where('is_active', true)
                ->whereNotNull('user_id')->orderBy('id')->get(),
            'categories' => VisitorCategory::where('is_active', true)->orderBy('name')->get(),
            'purposes' => VisitorPurpose::where('is_active', true)->orderBy('name')->get(),
            'defaultBranchId' => $branchId ?? Branch::where('is_active', true)->orderBy('name')->value('id'),
            'defaultHostId' => $employee->id,
        ]);
    }

    public function store(StoreVisitorInvitationBatchRequest $request)
    {
        [$batch, $generatedLinks] = $this->createBatch($request->user(), $request->validated());
        [, $emailFailures] = $this->sendRegistrationEmails($batch, $generatedLinks);

        return redirect()->route('visitor.invitation-links.show', $batch->id)
            ->with('success', count($generatedLinks) . ' guest registration link(s) created.')
            ->with('email_failures', $emailFailures);
    }

    public function storeApi(StoreVisitorInvitationBatchRequest $request): JsonResponse
    {
        [$batch, $generatedLinks] = $this->createBatch($request->user(), $request->validated());
        [$deliveryStatuses, $emailFailures] = $this->sendRegistrationEmails($batch, $generatedLinks);

        return response()->json([
            'message' => 'Guest registration links created.',
            'data' => [
                'id' => $batch->id,
                'name' => $batch->name,
                'visit_date' => $batch->visit_date->toDateString(),
                'expires_at' => $batch->expires_at->toIso8601String(),
                'links' => array_map(fn (array $link) => [
                    'id' => $link['id'],
                    'recipient_email' => $link['recipient_email'],
                    'delivery_status' => $deliveryStatuses[$link['id']],
                    'url' => $link['url'],
                ], $generatedLinks),
            ],
            'email_failures' => $emailFailures,
        ], 201);
    }

    public function indexApi(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $batches = VisitorInvitationBatch::query()
            ->where('created_by', $request->user()->id)
            ->with(['branch', 'host.user', 'category', 'purpose'])
            ->withCount('links')
            ->withCount([
                'links as submitted_links_count' => fn (Builder $query) => $query->whereNotNull('submitted_at'),
                'links as revoked_links_count' => fn (Builder $query) => $query->whereNotNull('revoked_at'),
            ])
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
        $batches->getCollection()->transform(fn (VisitorInvitationBatch $batch) => $this->batchSummary($batch));

        return response()->json($batches);
    }

    public function showApi(Request $request, int $batch): JsonResponse
    {
        $batch = VisitorInvitationBatch::query()
            ->where('created_by', $request->user()->id)
            ->with(['branch', 'host.user', 'category', 'purpose', 'links.visit.visitor', 'links.visits.visitor'])
            ->withCount('links')
            ->withCount([
                'links as submitted_links_count' => fn (Builder $query) => $query->whereNotNull('submitted_at'),
                'links as revoked_links_count' => fn (Builder $query) => $query->whereNotNull('revoked_at'),
            ])
            ->findOrFail($batch);
        $batchData = $this->batchSummary($batch);
        $batchData['notes'] = $batch->notes;
        $batchData['links'] = $batch->links->map(function (VisitorInvitationLink $link) use ($batch) {
            $available = !$link->submitted_at
                && !$link->revoked_at
                && $batch->expires_at->isFuture();

            return [
                'id' => $link->id,
                'recipient_email' => $link->recipient_email,
                'status' => $link->submitted_at
                    ? 'submitted'
                    : ($link->revoked_at
                        ? 'revoked'
                        : ($batch->expires_at->isPast() ? 'expired' : 'waiting_for_guest')),
                'submitted_at' => $link->submitted_at?->toIso8601String(),
                'url' => $available
                    ? route('visitor.public-registration.show', [
                        'token' => Crypt::decryptString($link->token_encrypted),
                    ])
                    : null,
                'visit_code' => $link->visit?->visit_code,
                'visitor' => $link->visit?->visitor ? [
                    'full_name' => $link->visit->visitor->full_name,
                    'phone' => $link->visit->visitor->phone,
                    'email' => $link->visit->visitor->email,
                    'company' => $link->visit->visitor->company,
                ] : null,
                'attendees' => $link->visits->map(fn (VisitorVisit $visit) => [
                    'visit_id' => $visit->id,
                    'visit_code' => $visit->visit_code,
                    'status' => $visit->status,
                    'full_name' => $visit->visitor?->full_name,
                    'phone' => $visit->visitor?->phone,
                    'email' => $visit->visitor?->email,
                    'company' => $visit->visitor?->company,
                ])->values(),
            ];
        })->values()->all();

        return response()->json(['data' => $batchData]);
    }

    private function createBatch(User $creator, array $data): array
    {
        $emails = $data['recipient_emails'];
        $shareLinkCount = (int) $data['share_link_count'];
        $visitDate = Carbon::createFromFormat('!Y-m-d', $data['visit_date']);
        $departureDate = Carbon::createFromFormat('!Y-m-d', $data['expected_departure_date'] ?? $data['visit_date']);
        $arrival = Carbon::createFromFormat(
            '!Y-m-d H:i',
            $data['visit_date'] . ' ' . $data['expected_arrival_time']
        );
        $departure = Carbon::createFromFormat(
            '!Y-m-d H:i',
            $departureDate->toDateString() . ' ' . $data['expected_departure_time']
        );
        $expiresAt = Carbon::createFromFormat('!Y-m-d', $data['expires_at'] ?? $data['visit_date'])->endOfDay();

        return DB::transaction(function () use (
            $creator,
            $data,
            $emails,
            $shareLinkCount,
            $visitDate,
            $arrival,
            $departure,
            $expiresAt
        ) {
            $batch = VisitorInvitationBatch::create([
                'created_by' => $creator->id,
                'name' => $data['name'],
                'branch_id' => $data['branch_id'],
                'host_employee_id' => $data['host_employee_id'],
                'visitor_category_id' => $data['visitor_category_id'],
                'visitor_purpose_id' => $data['visitor_purpose_id'],
                'visit_date' => $visitDate->toDateString(),
                'expected_arrival_at' => $arrival,
                'expected_departure_at' => $departure,
                'notes' => $data['notes'] ?? null,
                'expires_at' => $expiresAt,
            ]);

            $generatedLinks = [];
            $recipients = array_merge($emails, array_fill(0, $shareLinkCount, null));
            foreach ($recipients as $email) {
                $token = Str::random(64);
                $link = $batch->links()->create([
                    'token_hash' => hash('sha256', $token),
                    'token_encrypted' => Crypt::encryptString($token),
                    'recipient_email' => $email,
                ]);
                $generatedLinks[] = [
                    'id' => $link->id,
                    'url' => route('visitor.public-registration.show', ['token' => $token]),
                    'recipient_email' => $email,
                ];
            }

            return [$batch, $generatedLinks];
        });
    }

    private function sendRegistrationEmails(VisitorInvitationBatch $batch, array $generatedLinks): array
    {
        $deliveryStatuses = [];
        $emailFailures = [];
        foreach ($generatedLinks as $generatedLink) {
            if (!$generatedLink['recipient_email']) {
                $deliveryStatuses[$generatedLink['id']] = 'manual';
                continue;
            }

            try {
                Notification::route('mail', $generatedLink['recipient_email'])
                    ->notify(new VisitorRegistrationLinkNotification($batch, $generatedLink['url']));
                $deliveryStatuses[$generatedLink['id']] = 'sent';
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $exception) {
                $deliveryStatuses[$generatedLink['id']] = 'failed';
                $emailFailures[] = $generatedLink['recipient_email'];
                logger()->error('Visitor registration link email failed', [
                    'batch_id' => $batch->id,
                    'recipient' => $generatedLink['recipient_email'],
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return [$deliveryStatuses, $emailFailures];
    }

    private function batchSummary(VisitorInvitationBatch $batch): array
    {
        return [
            'id' => $batch->id,
            'name' => $batch->name,
            'branch' => $batch->branch ? [
                'id' => $batch->branch->id,
                'name' => $batch->branch->name,
            ] : null,
            'host' => $batch->host ? [
                'employee_id' => $batch->host->id,
                'name' => $batch->host->user?->name,
            ] : null,
            'category' => $batch->category ? [
                'id' => $batch->category->id,
                'name' => $batch->category->name,
            ] : null,
            'purpose' => $batch->purpose ? [
                'id' => $batch->purpose->id,
                'name' => $batch->purpose->name,
            ] : null,
            'visit_date' => $batch->visit_date->toDateString(),
            'expected_arrival_at' => $batch->expected_arrival_at->toIso8601String(),
            'expected_departure_at' => $batch->expected_departure_at->toIso8601String(),
            'expires_at' => $batch->expires_at->toIso8601String(),
            'links_count' => $batch->links_count,
            'submitted_links_count' => $batch->submitted_links_count ?? null,
            'revoked_links_count' => $batch->revoked_links_count ?? null,
        ];
    }

    public function show(Request $request, int $batch)
    {
        $batch = VisitorInvitationBatch::where('created_by', $request->user()->id)
            ->with(['branch', 'host.user', 'category', 'purpose', 'links.visit.visitor', 'links.visits.visitor'])
            ->findOrFail($batch);

        return view('visitor.invitation-links.show', [
            'title' => 'Guest Registration Links',
            'batch' => $batch,
            'generatedLinks' => $batch->links
                ->filter(fn (VisitorInvitationLink $link) => !$link->submitted_at
                    && !$link->revoked_at
                    && $batch->expires_at->isFuture())
                ->map(fn (VisitorInvitationLink $link) => [
                    'url' => route('visitor.public-registration.show', [
                        'token' => Crypt::decryptString($link->token_encrypted),
                    ]),
                    'recipient_email' => $link->recipient_email,
                    'id' => $link->id,
                ])->values(),
        ]);
    }

    public function revoke(Request $request, int $link)
    {
        $link = VisitorInvitationLink::whereHas('batch', fn (Builder $query) => $query->where('created_by', $request->user()->id))
            ->whereNull('submitted_at')
            ->whereNull('revoked_at')
            ->findOrFail($link);
        $link->update(['revoked_at' => now()]);

        return back()->with('success', 'Registration link revoked.');
    }

    public function setRecipientEmail(Request $request, int $link)
    {
        $data = $request->validate([
            'recipient_email' => ['required', 'email', 'max:255'],
        ]);
        DB::transaction(function () use ($request, $link, $data) {
            $link = VisitorInvitationLink::whereHas('batch', fn (Builder $query) => $query->where('created_by', $request->user()->id))
                ->whereNull('recipient_email')
                ->whereNull('submitted_at')
                ->whereNull('revoked_at')
                ->with('batch')
                ->lockForUpdate()
                ->findOrFail($link);
            abort_unless($link->batch->expires_at->isFuture(), 410, 'This registration link has expired.');

            $link->update(['recipient_email' => $data['recipient_email']]);
        });

        return back()->with('success', 'Recipient email saved.');
    }

    public function registrationForm(string $token)
    {
        $link = $this->findLink($token);
        if (!$link->isAvailable()) {
            return $this->unavailableRegistrationResponse();
        }

        return view('visitor.public-registration', [
            'batch' => $link->batch->load(['branch', 'host.user', 'category', 'purpose']),
            'token' => $token,
        ]);
    }

    public function submitRegistration(Request $request, string $token)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40', 'required_without:email'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'company' => ['nullable', 'string', 'max:255'],
            'bring_group' => ['nullable', 'boolean'],
            'group_members' => ['required_if:bring_group,1', 'array', 'min:1', 'max:20'],
            'group_members.*.full_name' => ['required', 'string', 'max:255'],
            'group_members.*.phone' => ['nullable', 'string', 'max:40'],
            'group_members.*.email' => ['nullable', 'email', 'max:255'],
        ]);
        $groupMembers = ($data['bring_group'] ?? false) ? ($data['group_members'] ?? []) : [];
        unset($data['bring_group'], $data['group_members']);

        $visit = DB::transaction(function () use ($token, $data, $groupMembers) {
            $link = VisitorInvitationLink::where('token_hash', hash('sha256', $token))
                ->lockForUpdate()->firstOrFail();
            $link->load('batch');
            if (!$link->isAvailable()) {
                return null;
            }

            $batch = $link->batch;
            $creator = User::findOrFail($batch->created_by);
            $visitDetails = [
                'visitor_category_id' => $batch->visitor_category_id,
                'visitor_purpose_id' => $batch->visitor_purpose_id,
                'branch_id' => $batch->branch_id,
                'host_employee_id' => $batch->host_employee_id,
                'visit_date' => $batch->visit_date->toDateString(),
                'expected_arrival_date' => $batch->expected_arrival_at->format('d F Y'),
                'expected_arrival_time' => $batch->expected_arrival_at->format('H:i'),
                'expected_departure_date' => $batch->expected_departure_at->format('d F Y'),
                'expected_departure_time' => $batch->expected_departure_at->format('H:i'),
                'notes' => $batch->notes,
                'visitor_invitation_link_id' => $link->id,
            ];
            $created = $this->visitorService->createVisit(
                $creator,
                array_merge($data, $visitDetails),
                'shared_invitation'
            );
            $primaryVisit = $created['visit'];
            foreach ($groupMembers as $groupMember) {
                $this->visitorService->createVisit(
                    $creator,
                    array_merge($data, ['phone' => null, 'email' => null], $groupMember, $visitDetails),
                    'shared_invitation'
                );
            }

            $link->update([
                'visitor_visit_id' => $primaryVisit->id,
                'submitted_at' => now(),
            ]);

            return $primaryVisit;
        });

        if (!$visit) {
            return $this->unavailableRegistrationResponse();
        }

        return view('visitor.registration-submitted', ['visit' => $visit]);
    }

    private function findLink(string $token): VisitorInvitationLink
    {
        abort_unless(strlen($token) === 64, 404);

        return VisitorInvitationLink::with('batch')
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();
    }

    private function unavailableRegistrationResponse()
    {
        return response()->view('visitor.registration-unavailable', [], 410);
    }
}
