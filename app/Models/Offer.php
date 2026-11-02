<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Offer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'basic_salary' => 'encrypted',
        'allowances' => 'encrypted:array',
        'proposed_start_date' => 'date',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'version' => 'integer',
    ];

    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(OfferApprovalStep::class)->orderBy('level')->orderBy('id');
    }

    public function onboarding(): HasOne
    {
        return $this->hasOne(ApplicationOnboarding::class);
    }

    /**
     * Strict compensation visibility check:
     * 1. Recruitment Administrator
     * 2. Recruitment Monitor
     * 3. Approver assigned to this offer
     */
    public function canViewCompensation(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isRecruitmentAdministrator() || $user->isRecruitmentMonitor()) {
            return true;
        }

        $employeeId = $user->employee?->id;
        if (!$employeeId) {
            return false;
        }

        return $this->approvalSteps()->where('approver_employee_id', $employeeId)->exists();
    }

    public function getFormattedBasicSalaryFor(?User $user): string
    {
        if (!$this->canViewCompensation($user)) {
            return '[Restricted]';
        }

        $amount = (float) $this->basic_salary;
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function getFormattedAllowancesFor(?User $user): array
    {
        if (!$this->canViewCompensation($user)) {
            return ['status' => '[Restricted]'];
        }

        return is_array($this->allowances) ? $this->allowances : [];
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function areAllStepsApproved(): bool
    {
        $steps = $this->approvalSteps;
        if ($steps->isEmpty()) {
            return false;
        }

        return $steps->every(fn (OfferApprovalStep $step) => $step->status === 'approved');
    }

    public function currentActiveLevel(): ?int
    {
        $pendingStep = $this->approvalSteps()
            ->where('status', 'pending')
            ->orderBy('level')
            ->first();

        return $pendingStep ? (int) $pendingStep->level : null;
    }

    public function canEmployeeApprove(int $employeeId): bool
    {
        if ($this->status !== 'pending_approval') {
            return false;
        }

        $activeLevel = $this->currentActiveLevel();
        if ($activeLevel === null) {
            return false;
        }

        // Must be a pending step at the currently active level
        return $this->approvalSteps()
            ->where('approver_employee_id', $employeeId)
            ->where('level', $activeLevel)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Prevent salary leaking into JSON if unauthorized.
     */
    public function toArray()
    {
        $array = parent::toArray();
        $user = auth()->user();

        if (!$this->canViewCompensation($user)) {
            $array['basic_salary'] = '[Restricted]';
            $array['allowances'] = '[Restricted]';
        }

        return $array;
    }
}

