<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorInvitationBatch extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'visit_date' => 'date',
        'expected_arrival_at' => 'datetime',
        'expected_departure_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'host_employee_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VisitorCategory::class, 'visitor_category_id');
    }

    public function purpose(): BelongsTo
    {
        return $this->belongsTo(VisitorPurpose::class, 'visitor_purpose_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(VisitorInvitationLink::class, 'batch_id');
    }
}
