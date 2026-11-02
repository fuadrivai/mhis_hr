<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewRound extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'round_no'     => 'integer',
    ];

    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class, 'candidate_application_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function roundInterviewers(): HasMany
    {
        return $this->hasMany(InterviewRoundInterviewer::class, 'interview_round_id');
    }

    public function interviewers(): BelongsToMany
    {
        return $this->belongsToMany(
            Employee::class,
            'interview_round_interviewers',
            'interview_round_id',
            'employee_id'
        )->withTimestamps();
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(InterviewEvaluation::class, 'interview_round_id');
    }

    public function allEvaluationsSubmitted(): bool
    {
        $interviewerCount = $this->interviewers()->count();
        if ($interviewerCount === 0) {
            return false;
        }

        $submittedCount = $this->evaluations()->where('status', 'submitted')->count();
        return $submittedCount >= $interviewerCount;
    }

    public function getRoundTypeLabelAttribute(): string
    {
        return match ($this->round_type) {
            'hr'    => 'HR Interview',
            'user'  => 'User / Hiring Manager',
            'panel' => 'Panel Interview',
            default => strtoupper((string) $this->round_type),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'done'      => 'success',
            'scheduled' => 'info',
            'cancelled' => 'default',
            default     => 'primary',
        };
    }
}

