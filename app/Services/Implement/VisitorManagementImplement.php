<?php

namespace App\Services\Implement;

use App\Models\Branch;
use App\Models\Session;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorApproval;
use App\Models\VisitorVisit;
use App\Models\VisitorVisitLog;
use App\Notifications\VisitorInvitationForGuest;
use App\Notifications\VisitorVisitNotification;
use App\Services\VisitorManagementService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VisitorManagementImplement implements VisitorManagementService
{
    public function createVisit(User $user, array $data, string $method): array
    {
        $data = $this->normalizeSchedule($data);
        $this->authorizeRegistrationBranch($user, $data);

        $created = DB::transaction(function () use ($user, $data, $method) {
            $visitorQuery = Visitor::query();
            if (!empty($data['email']) || !empty($data['phone'])) {
                $visitorQuery->where(function ($query) use ($data) {
                    if (!empty($data['email'])) {
                        $query->where('email', $data['email']);
                    }
                    if (!empty($data['phone']) && empty($data['email'])) {
                        $query->where('phone', $data['phone'])
                            ->where('full_name', $data['full_name']);
                    } elseif (!empty($data['phone']) && !empty($data['email'])) {
                        $query->orWhere(function ($phoneQuery) use ($data) {
                            $phoneQuery->where('phone', $data['phone'])
                                ->where('full_name', $data['full_name']);
                        });
                    }
                });
            } else {
                $visitorQuery->whereRaw('1 = 0');
            }

            $visitor = $visitorQuery->lockForUpdate()->first();
            if (!$visitor) {
                $visitor = Visitor::create([
                    'visitor_code' => 'VIS-' . now()->format('Y') . '-' . Str::upper(Str::random(8)),
                    'full_name' => $data['full_name'],
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'company' => $data['company'] ?? null,
                    'photo_path' => $data['photo_path'] ?? null,
                ]);
            } else {
                $visitor->fill([
                    'full_name' => $data['full_name'],
                    'phone' => $data['phone'] ?? $visitor->phone,
                    'email' => $data['email'] ?? $visitor->email,
                    'company' => $data['company'] ?? $visitor->company,
                    'photo_path' => $data['photo_path'] ?? $visitor->photo_path,
                ])->save();
            }

            $category = \App\Models\VisitorCategory::findOrFail($data['visitor_category_id']);
            $purpose = \App\Models\VisitorPurpose::findOrFail($data['visitor_purpose_id']);
            $requiresApproval = $category->requires_approval || $purpose->requires_approval;
            $qrToken = Str::random(64);

            $visit = VisitorVisit::create([
                'visit_code' => $this->generateVisitCode(),
                'visitor_id' => $visitor->id,
                'branch_id' => $data['branch_id'],
                'host_employee_id' => $data['host_employee_id'],
                'visitor_category_id' => $category->id,
                'visitor_purpose_id' => $purpose->id,
                'registered_by' => $user->id,
                'registration_method' => $method,
                'visit_date' => $data['visit_date'],
                'expected_arrival_at' => $data['expected_arrival_at'] ?? null,
                'expected_departure_at' => $data['expected_departure_at'] ?? null,
                'status' => $requiresApproval ? 'pending_confirmation' : 'expected',
                'qr_token_hash' => hash('sha256', $qrToken),
                'notes' => $data['notes'] ?? null,
                'metadata' => $requiresApproval
                    ? ['pending_invitation_token' => Crypt::encryptString($qrToken)]
                    : null,
            ]);

            if ($requiresApproval) {
                VisitorApproval::create([
                    'visitor_visit_id' => $visit->id,
                    'approver_employee_id' => $visit->host_employee_id,
                    'status' => 'pending',
                ]);
            }

            $this->log($visit, $user, 'registered', null, $visit->status, 'Visit registered.');
            DB::afterCommit(function () use ($visit, $requiresApproval, $method) {
                $hostUser = $visit->host()->with('user')->first()?->user;
                if ($hostUser) {
                    $hostUser->notify(new VisitorVisitNotification($visit, $requiresApproval ? 'approval' : 'invitation'));

                    if ($method === 'employee_invitation') {
                        $this->sendHostPushNotification($hostUser->id, $visit, $requiresApproval);
                    }
                }
            });

            return [
                'visit' => $visit->load(['visitor', 'branch', 'host.personal', 'category', 'purpose']),
                'qr_token' => $qrToken,
            ];
        });

        if ($created['visit']->status === 'expected') {
            $this->sendGuestInvitation($created['visit'], $created['qr_token']);
        }

        return $created;
    }

    public function processApproval(User $user, VisitorVisit $visit, string $decision, ?string $notes): VisitorVisit
    {
        $visit = DB::transaction(function () use ($user, $visit, $decision, $notes) {
            $visit = VisitorVisit::whereKey($visit->id)->lockForUpdate()->firstOrFail();
            if ($visit->status !== 'pending_confirmation') {
                throw ValidationException::withMessages(['visit' => 'This visit is no longer waiting for approval.']);
            }

            $approval = VisitorApproval::where('visitor_visit_id', $visit->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $visit->status;
            $visit->status = $decision === 'approve' ? 'expected' : 'rejected';
            if ($decision !== 'approve' && is_array($visit->metadata)) {
                unset($visit->metadata['pending_invitation_token']);
                $visit->metadata = $visit->metadata ?: null;
            }
            $visit->save();

            $approval->update([
                'processed_by' => $user->id,
                'status' => $decision === 'approve' ? 'approved' : 'rejected',
                'notes' => $notes,
                'processed_at' => now(),
            ]);

            $this->log(
                $visit,
                $user,
                $decision === 'approve' ? 'approved' : 'rejected',
                $oldStatus,
                $visit->status,
                $notes
            );

            return $visit->load(['visitor', 'branch', 'host.personal', 'category', 'purpose']);
        });

        if ($decision === 'approve' && is_array($visit->metadata)) {
            $encryptedToken = $visit->metadata['pending_invitation_token'] ?? null;
            if ($encryptedToken) {
                $this->sendGuestInvitation($visit, Crypt::decryptString($encryptedToken));
                $visit->metadata = null;
                $visit->save();
            }
        }

        return $visit;
    }

    private function normalizeSchedule(array $data): array
    {
        foreach (['arrival', 'departure'] as $period) {
            $dateKey = 'expected_' . $period . '_date';
            $timeKey = 'expected_' . $period . '_time';
            if (isset($data[$dateKey], $data[$timeKey])) {
                $data['expected_' . $period . '_at'] = \Carbon\Carbon::createFromFormat(
                    '!d F Y H:i',
                    $data[$dateKey] . ' ' . $data[$timeKey]
                )->format('Y-m-d H:i:s');
                unset($data[$dateKey], $data[$timeKey]);
            }
        }

        return $data;
    }

    private function generateVisitCode(): string
    {
        do {
            $code = Str::upper(Str::random(5));
        } while (VisitorVisit::where('visit_code', $code)->exists());

        return $code;
    }

    private function authorizeRegistrationBranch(User $user, array $data): void
    {
        if ($user->hasRole('admin') || $user->roles()->where('roles.id', 1)->exists()) {
            return;
        }

        $branchId = $user->employee?->employment?->branch_id;
        if (!$branchId || (int) $branchId !== (int) $data['branch_id']) {
            throw new AuthorizationException('You are not allowed to register visitors for this branch.');
        }
    }

    private function sendGuestInvitation(VisitorVisit $visit, string $qrToken): void
    {
        $visit->loadMissing('visitor');
        if (!$visit->visitor->email) {
            return;
        }

        Notification::route('mail', $visit->visitor->email)
            ->notify(new VisitorInvitationForGuest($visit, $qrToken));
    }

    private function sendHostPushNotification(int $userId, VisitorVisit $visit, bool $requiresApproval): void
    {
        $visit->loadMissing('visitor');
        $sessions = Session::where('user_id', $userId)
            ->where('device', 'android')
            ->get();

        $data = [
            'title' => $requiresApproval ? 'Visitor Approval Request Pending' : 'New Visitor Invitation',
            'body' => $requiresApproval
                ? 'You have a new visitor request to approve for ' . $visit->visitor->full_name . '.'
                : 'You have a new visitor invitation for ' . $visit->visitor->full_name
                    . ' on ' . $visit->expected_arrival_at?->format('d M Y H:i') . '.',
        ];

        foreach ($sessions as $session) {
            if (empty($session->device_id)) {
                continue;
            }

            $result = sendMessage($session->device_id, $data);
            $context = [
                'session_id' => $session->id,
                'visit_id' => $visit->id,
                'result' => $result,
            ];

            if ($result['success'] ?? false) {
                logger()->info('Visitor invitation FCM sent', $context);
            } else {
                logger()->warning('Visitor invitation FCM failed', $context);
            }
        }
    }

    public function checkIn(User $user, VisitorVisit $visit, array $data): VisitorVisit
    {
        return DB::transaction(function () use ($user, $visit, $data) {
            Branch::whereKey($visit->branch_id)->lockForUpdate()->firstOrFail();
            $visit = VisitorVisit::whereKey($visit->id)->lockForUpdate()->firstOrFail();

            if ($visit->status !== 'expected') {
                throw ValidationException::withMessages(['visit' => 'Only approved expected visits can be checked in.']);
            }
            if (!$visit->visit_date->isToday()) {
                throw ValidationException::withMessages(['visit' => 'Check-in is only available on the scheduled visit date.']);
            }

            $badge = $data['badge_number'] ?? null;
            if ($badge) {
                $badgeInUse = VisitorVisit::where('branch_id', $visit->branch_id)
                    ->where('badge_number', $badge)
                    ->where('status', 'checked_in')
                    ->where('id', '<>', $visit->id)
                    ->exists();
                if ($badgeInUse) {
                    throw ValidationException::withMessages(['badge_number' => 'This badge is already assigned to a visitor currently inside.']);
                }
            }

            $oldStatus = $visit->status;
            $visit->update([
                'check_in_at' => now(),
                'check_in_by' => $user->id,
                'check_in_method' => 'security_desk',
                'badge_number' => $badge,
                'check_in_photo' => $data['check_in_photo'] ?? null,
                'check_in_notes' => $data['notes'] ?? null,
                'status' => 'checked_in',
            ]);

            $this->log($visit, $user, 'checked_in', $oldStatus, 'checked_in', $data['notes'] ?? null);
            DB::afterCommit(function () use ($visit) {
                $hostUser = $visit->host()->with('user')->first()?->user;
                if ($hostUser) {
                    $hostUser->notify(new VisitorVisitNotification($visit, 'arrival'));
                }
            });

            return $visit->fresh(['visitor', 'branch', 'host.personal', 'category', 'purpose']);
        });
    }

    public function checkOut(User $user, VisitorVisit $visit, array $data): VisitorVisit
    {
        return DB::transaction(function () use ($user, $visit, $data) {
            Branch::whereKey($visit->branch_id)->lockForUpdate()->firstOrFail();
            $visit = VisitorVisit::whereKey($visit->id)->lockForUpdate()->firstOrFail();

            if ($visit->status !== 'checked_in') {
                throw ValidationException::withMessages(['visit' => 'Only visitors currently inside can be checked out.']);
            }
            if ($visit->badge_number && empty($data['badge_return_status'])) {
                throw ValidationException::withMessages(['badge_return_status' => 'Select the badge return status.']);
            }

            $visit->update([
                'check_out_at' => now(),
                'check_out_by' => $user->id,
                'check_out_method' => 'security_desk',
                'badge_return_status' => $data['badge_return_status'] ?? 'not_applicable',
                'check_out_notes' => $data['notes'] ?? null,
                'status' => 'completed',
            ]);

            $this->log($visit, $user, 'checked_out', 'checked_in', 'completed', $data['notes'] ?? null);

            return $visit->fresh(['visitor', 'branch', 'host.personal', 'category', 'purpose']);
        });
    }

    public function correctCheckOut(User $user, VisitorVisit $visit, string $reason): VisitorVisit
    {
        return DB::transaction(function () use ($user, $visit, $reason) {
            Branch::whereKey($visit->branch_id)->lockForUpdate()->firstOrFail();
            $visit = VisitorVisit::whereKey($visit->id)->lockForUpdate()->firstOrFail();
            if ($visit->status !== 'checked_in') {
                throw ValidationException::withMessages(['visit' => 'Only a visit currently checked in can be corrected.']);
            }

            $visit->update([
                'check_out_at' => now(),
                'check_out_by' => $user->id,
                'check_out_method' => 'manual_correction',
                'check_out_notes' => $reason,
                'status' => 'completed',
            ]);
            $this->log($visit, $user, 'manual_check_out', 'checked_in', 'completed', $reason);

            return $visit->fresh(['visitor', 'branch', 'host.personal', 'category', 'purpose']);
        });
    }

    public function cancelVisit(User $user, VisitorVisit $visit, string $reason): VisitorVisit
    {
        return DB::transaction(function () use ($user, $visit, $reason) {
            $visit = VisitorVisit::whereKey($visit->id)->lockForUpdate()->firstOrFail();
            if (!in_array($visit->status, ['expected', 'pending_confirmation'], true) || $visit->visit_date->lt(today())) {
                throw ValidationException::withMessages(['visit' => 'This visit can no longer be cancelled.']);
            }

            $oldStatus = $visit->status;
            $visit->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
            ]);
            VisitorApproval::where('visitor_visit_id', $visit->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled', 'processed_by' => $user->id, 'processed_at' => now()]);
            $this->log($visit, $user, 'cancelled', $oldStatus, 'cancelled', $reason);

            return $visit->fresh(['visitor', 'branch', 'host.personal', 'category', 'purpose']);
        });
    }

    private function log(
        VisitorVisit $visit,
        ?User $user,
        string $action,
        ?string $oldStatus,
        ?string $newStatus,
        ?string $description
    ): void {
        VisitorVisitLog::create([
            'visitor_visit_id' => $visit->id,
            'user_id' => $user?->id,
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'description' => $description,
        ]);
    }
}
