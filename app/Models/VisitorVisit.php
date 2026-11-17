<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorVisit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'visit_date' => 'date',
        'expected_arrival_at' => 'datetime',
        'expected_departure_at' => 'datetime',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'host_employee_id');
    }

    public function invitationLink(): BelongsTo
    {
        return $this->belongsTo(VisitorInvitationLink::class, 'visitor_invitation_link_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VisitorCategory::class, 'visitor_category_id');
    }

    public function purpose(): BelongsTo
    {
        return $this->belongsTo(VisitorPurpose::class, 'visitor_purpose_id');
    }

    public function monitorEmployees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'visitor_visit_monitors', 'visitor_visit_id', 'employee_id')
            ->withTimestamps();
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(VisitorApproval::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VisitorVisitLog::class);
    }

    public function scopeForBranch($query, ?int $branchId)
    {
        return $query->when($branchId, fn ($builder) => $builder->where('branch_id', $branchId));
    }
}
