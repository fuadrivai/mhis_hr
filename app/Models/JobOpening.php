<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOpening extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'opened_at' => 'date',
        'closes_at' => 'date',
        'headcount' => 'integer',
    ];

    public function jobRequirement(): BelongsTo
    {
        return $this->belongsTo(JobRequirement::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function hiringManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'hiring_manager_employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CandidateApplication::class);
    }

    public function getFilledCountAttribute(): int
    {
        if ($this->relationLoaded('applications')) {
            return $this->applications->where('stage', 'joined')->count();
        }

        return $this->applications()->where('stage', 'joined')->count();
    }

    public function getActiveApplicationsCountAttribute(): int
    {
        if ($this->relationLoaded('applications')) {
            return $this->applications->whereNotIn('stage', ['rejected', 'withdrawn'])->count();
        }

        return $this->applications()->whereNotIn('stage', ['rejected', 'withdrawn'])->count();
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isFilled(): bool
    {
        return $this->status === 'filled';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check and update status based on headcount and joined applications.
     */
    public function syncHeadcountStatus(): void
    {
        $filledCount = $this->filled_count;

        if ($filledCount >= $this->headcount && $this->status === 'open') {
            $this->update(['status' => 'filled']);
        } elseif ($filledCount < $this->headcount && $this->status === 'filled') {
            $this->update(['status' => 'open']);
        }
    }
}

