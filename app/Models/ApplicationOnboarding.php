<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationOnboarding extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'contract_sent_at' => 'datetime',
        'contract_signed_at' => 'datetime',
        'join_date' => 'date',
        'postponed_to' => 'date',
    ];

    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isContractSigned(): bool
    {
        return $this->contract_status === 'signed';
    }

    public function isJoined(): bool
    {
        return $this->join_status === 'joined';
    }

    public function canCreateEmployeeDraft(): bool
    {
        return $this->join_status === 'joined' && empty($this->employee_id);
    }

    public function getContractStatusBadgeAttribute(): string
    {
        return match ($this->contract_status) {
            'signed'  => '<span class="badge badge-success" style="background-color: #26B99A;">Signed</span>',
            'sent'    => '<span class="badge badge-info" style="background-color: #337ab7;">Sent</span>',
            default   => '<span class="badge badge-default" style="background-color: #777;">Pending</span>',
        };
    }

    public function getJoinStatusBadgeAttribute(): string
    {
        return match ($this->join_status) {
            'joined'    => '<span class="badge badge-success" style="background-color: #26B99A;">Joined</span>',
            'no_show'   => '<span class="badge badge-danger" style="background-color: #d9534f;">No Show</span>',
            'postponed' => '<span class="badge badge-warning" style="background-color: #f0ad4e;">Postponed</span>',
            default     => '<span class="badge badge-default" style="background-color: #777;">Pending</span>',
        };
    }
}

