<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CvMatchResult extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'total_score'          => 'integer',
        'mandatory_missing'    => 'boolean',
        'red_flags'            => 'array',
        'interview_questions'  => 'array',
        'requirement_snapshot' => 'array',
        'processed_at'         => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class, 'candidate_application_id');
    }

    public function opening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class, 'job_opening_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(JobRequirement::class, 'job_requirement_id');
    }

    public function requirementResults(): HasMany
    {
        return $this->hasMany(CvMatchRequirementResult::class, 'cv_match_result_id');
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(CvMatchOverride::class, 'cv_match_result_id')->latest();
    }

    public function latestOverride(): HasOne
    {
        return $this->hasOne(CvMatchOverride::class, 'cv_match_result_id')->latestOfMany();
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function getEffectiveScoreAttribute(): ?int
    {
        if ($this->relationLoaded('latestOverride') && $this->latestOverride) {
            return $this->latestOverride->override_score ?? $this->total_score;
        }

        $latest = $this->latestOverride;
        if ($latest && $latest->override_score !== null) {
            return (int) $latest->override_score;
        }

        return $this->total_score !== null ? (int) $this->total_score : null;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isNeedsReview(): bool
    {
        return $this->status === 'needs_manual_review';
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, ['queued', 'processing'], true);
    }
}

