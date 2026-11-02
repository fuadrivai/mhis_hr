<?php

namespace App\Services\Implement;

use App\Models\ApplicationOnboarding;
use App\Models\ApplicationStageHistory;
use App\Models\CandidateApplication;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobOpening;
use App\Models\Organization;
use App\Models\Personal;
use App\Models\User;
use App\Services\RecruitmentOnboardingService;
use App\Services\Recruitment\StageTransitionGuard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecruitmentOnboardingImplement implements RecruitmentOnboardingService
{
    protected StageTransitionGuard $transitionGuard;

    public function __construct(StageTransitionGuard $transitionGuard)
    {
        $this->transitionGuard = $transitionGuard;
    }

    protected function authorizeRecruitment(User $user): void
    {
        if (!$user->canAccessRecruitment()) {
            throw new AuthorizationException('You are not authorized to manage candidate onboarding.');
        }
    }

    public function updateContract(
        CandidateApplication $application,
        array $data,
        ?UploadedFile $contractFile,
        User $user
    ): ApplicationOnboarding {
        $this->authorizeRecruitment($user);

        return DB::transaction(function () use ($application, $data, $contractFile, $user) {
            $onboarding = ApplicationOnboarding::firstOrCreate(
                ['candidate_application_id' => $application->id],
                [
                    'contract_status' => 'pending',
                    'join_status'     => 'pending',
                ]
            );

            $status = $data['contract_status'] ?? $onboarding->contract_status;
            $contractSentAt = $onboarding->contract_sent_at;
            $contractSignedAt = $onboarding->contract_signed_at;

            if ($status === 'sent' && !$contractSentAt) {
                $contractSentAt = now();
            } elseif ($status === 'signed' && !$contractSignedAt) {
                $contractSignedAt = now();
            }

            $filePath = $onboarding->contract_file_path;
            if ($contractFile) {
                if ($contractFile->getClientMimeType() !== 'application/pdf') {
                    throw ValidationException::withMessages([
                        'contract_file' => 'Signed contract document must be a PDF file.',
                    ]);
                }

                if ($contractFile->getSize() > 10 * 1024 * 1024) {
                    throw ValidationException::withMessages([
                        'contract_file' => 'Contract document size exceeds the 10 MB limit.',
                    ]);
                }

                $filename = Str::uuid() . '.pdf';
                $filePath = $contractFile->storeAs('recruitment/contracts', $filename);
            }

            $contractUrl = array_key_exists('contract_url', $data)
                ? (trim((string) $data['contract_url']) ?: null)
                : $onboarding->contract_url;

            $notes = array_key_exists('notes', $data)
                ? (trim((string) $data['notes']) ?: null)
                : $onboarding->notes;

            $onboarding->update([
                'contract_status'    => $status,
                'contract_sent_at'   => $contractSentAt,
                'contract_signed_at' => $contractSignedAt,
                'contract_file_path' => $filePath,
                'contract_url'       => $contractUrl,
                'notes'              => $notes,
            ]);

            // If moving to contract_signing stage
            if ($status === 'signed' || $status === 'sent') {
                if ($application->stage === 'offer') {
                    $this->transitionGuard->validateTransition(
                        $application,
                        'contract_signing',
                        $status,
                        'Contract ' . $status,
                        $user
                    );

                    $fromStage = $application->stage;
                    $application->update([
                        'stage'            => 'contract_signing',
                        'status'           => StageTransitionGuard::mapAggregateStatus('contract_signing'),
                        'stage_substatus'  => $status,
                        'last_activity_at' => now(),
                    ]);

                    ApplicationStageHistory::create([
                        'candidate_application_id' => $application->id,
                        'from_stage'               => $fromStage,
                        'to_stage'                 => 'contract_signing',
                        'from_substatus'           => $application->stage_substatus,
                        'to_substatus'             => $status,
                        'changed_by'               => $user->id,
                        'reason'                   => "Contract status updated to {$status}",
                        'changed_at'               => now(),
                    ]);
                }
            }

            return $onboarding->refresh();
        });
    }

    public function updateJoinStatus(
        CandidateApplication $application,
        array $data,
        User $user
    ): ApplicationOnboarding {
        $this->authorizeRecruitment($user);

        return DB::transaction(function () use ($application, $data, $user) {
            $onboarding = ApplicationOnboarding::firstOrCreate(
                ['candidate_application_id' => $application->id],
                [
                    'contract_status' => 'pending',
                    'join_status'     => 'pending',
                ]
            );

            $joinStatus = $data['join_status'] ?? $onboarding->join_status;
            $joinDate = !empty($data['join_date']) ? $data['join_date'] : $onboarding->join_date;
            $postponedTo = !empty($data['postponed_to']) ? $data['postponed_to'] : null;
            $reason = !empty($data['reason']) ? trim($data['reason']) : "Join status updated to {$joinStatus}";

            if ($joinStatus === 'postponed' && empty($postponedTo)) {
                throw ValidationException::withMessages([
                    'postponed_to' => 'A postponed date is required when joining is postponed.',
                ]);
            }

            $onboarding->update([
                'join_status'  => $joinStatus,
                'join_date'    => $joinDate,
                'postponed_to' => $postponedTo,
                'notes'        => !empty($data['notes']) ? trim($data['notes']) : $onboarding->notes,
            ]);

            // Handling stage transitions based on join_status
            if ($joinStatus === 'joined') {
                $this->transitionGuard->validateTransition(
                    $application,
                    'joined',
                    'joined',
                    $reason,
                    $user
                );

                $fromStage = $application->stage;
                $application->update([
                    'stage'            => 'joined',
                    'status'           => StageTransitionGuard::mapAggregateStatus('joined'),
                    'stage_substatus'  => 'joined',
                    'last_activity_at' => now(),
                ]);

                ApplicationStageHistory::create([
                    'candidate_application_id' => $application->id,
                    'from_stage'               => $fromStage,
                    'to_stage'                 => 'joined',
                    'from_substatus'           => $application->stage_substatus,
                    'to_substatus'             => 'joined',
                    'changed_by'               => $user->id,
                    'reason'                   => $reason,
                    'changed_at'               => now(),
                ]);

                // Sync opening headcount filled status
                if ($application->job_opening_id) {
                    $opening = JobOpening::find($application->job_opening_id);
                    $opening?->syncHeadcountStatus();
                }
            } elseif ($joinStatus === 'no_show') {
                // No show can transition stage to withdrawn with mandatory explanation
                $fromStage = $application->stage;
                $application->update([
                    'stage'            => 'withdrawn',
                    'status'           => StageTransitionGuard::mapAggregateStatus('withdrawn'),
                    'stage_substatus'  => 'no_show',
                    'last_activity_at' => now(),
                ]);

                ApplicationStageHistory::create([
                    'candidate_application_id' => $application->id,
                    'from_stage'               => $fromStage,
                    'to_stage'                 => 'withdrawn',
                    'from_substatus'           => $application->stage_substatus,
                    'to_substatus'             => 'no_show',
                    'changed_by'               => $user->id,
                    'reason'                   => "Candidate did not show up on join date: {$reason}",
                    'changed_at'               => now(),
                ]);
            } elseif ($joinStatus === 'postponed') {
                $application->update([
                    'stage_substatus'  => 'postponed',
                    'last_activity_at' => now(),
                ]);
            }

            return $onboarding->refresh();
        });
    }

    public function createEmployeeDraft(
        CandidateApplication $application,
        User $user
    ): Employee {
        $this->authorizeRecruitment($user);

        $onboarding = $application->onboarding;
        if (!$onboarding || $onboarding->join_status !== 'joined') {
            throw ValidationException::withMessages([
                'onboarding' => 'An employee draft can only be created once the candidate has joined.',
            ]);
        }

        // Idempotency check: Cannot create duplicate employee from same application
        if (!empty($onboarding->employee_id)) {
            $existing = Employee::find($onboarding->employee_id);
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($application, $onboarding, $user) {
            $candidate = $application->candidate;

            // Split full name into first and last name
            $parts = explode(' ', trim($candidate->full_name), 2);
            $firstName = $parts[0] ?? $candidate->full_name;
            $lastName = $parts[1] ?? '';

            // 1. Personal record (avoid duplicating email if personal already exists)
            $personal = Personal::where('email', $candidate->email)->first();
            if (!$personal) {
                $personal = Personal::create([
                    'fullname'        => $candidate->full_name,
                    'first_name'      => $firstName,
                    'last_name'       => $lastName,
                    'email'           => $candidate->email,
                    'phone'           => $candidate->phone,
                    'mobile_phone'    => $candidate->phone,
                    'address'         => $candidate->current_city,
                    'current_address' => $candidate->current_city,
                    'religion_id'     => 2, // Default: Others
                ]);
            }

            // 2. Employment record
            $orgId = null;
            if (!empty($application->division)) {
                $matchedOrg = Organization::where('name', 'like', '%' . $application->division . '%')->first();
                $orgId = $matchedOrg?->id;
            }

            $positionName = $application->position?->name ?: ($application->position_applied ?: 'Employee');
            $branchName = $application->branch?->name;

            $employment = Employment::create([
                'employee_id'        => 'EMP-DRAFT-' . $candidate->id,
                'organization_id'    => $orgId,
                'organization_name'  => $application->division,
                'job_position_id'    => $application->position_id,
                'job_position_name'  => $positionName,
                'branch_id'          => $application->branch_id,
                'branch_name'        => $branchName,
                'employment_status'  => 'contract',
                'join_date'          => $onboarding->join_date,
                'status'             => 0, // Inactive draft
            ]);

            // 3. Employee record (draft)
            $employee = Employee::create([
                'personal_id'     => $personal->id,
                'employment_id'   => $employment->id,
                'user_id'         => null, // No login credentials generated in draft
                'payrol_info_id'  => null, // Confidential salary is not copied automatically
                'is_active'       => 0,    // Inactive draft
                'employee_status' => 'draft',
            ]);

            // 4. Link employee_id back to application_onboardings
            $onboarding->update([
                'employee_id' => $employee->id,
            ]);

            // 5. Stage history audit
            ApplicationStageHistory::create([
                'candidate_application_id' => $application->id,
                'from_stage'               => $application->stage,
                'to_stage'                 => $application->stage,
                'from_substatus'           => $application->stage_substatus,
                'to_substatus'             => 'employee_draft_created',
                'changed_by'               => $user->id,
                'reason'                   => "Created Employee Draft #{$employee->id} (Code: EMP-DRAFT-{$candidate->id})",
                'changed_at'               => now(),
            ]);

            return $employee;
        });
    }

    public function getContractFilePath(ApplicationOnboarding $onboarding): ?string
    {
        if (empty($onboarding->contract_file_path)) {
            return null;
        }

        if (Storage::exists($onboarding->contract_file_path)) {
            return Storage::path($onboarding->contract_file_path);
        }

        return null;
    }
}

