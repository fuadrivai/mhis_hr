<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorInvitationLink extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'submitted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(VisitorInvitationBatch::class, 'batch_id');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitorVisit::class, 'visitor_visit_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(VisitorVisit::class, 'visitor_invitation_link_id');
    }

    public function isAvailable(): bool
    {
        return !$this->submitted_at
            && !$this->revoked_at
            && $this->batch->expires_at->isFuture();
    }
}
