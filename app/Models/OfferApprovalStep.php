<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferApprovalStep extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'level' => 'integer',
        'acted_at' => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_employee_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved' => '<span class="badge badge-success" style="background-color: #26B99A;">Approved</span>',
            'rejected' => '<span class="badge badge-danger" style="background-color: #d9534f;">Rejected</span>',
            'skipped'  => '<span class="badge badge-secondary" style="background-color: #777;">Skipped</span>',
            default    => '<span class="badge badge-warning" style="background-color: #f0ad4e;">Pending</span>',
        };
    }
}

