<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewEvaluation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function interviewRound(): BelongsTo
    {
        return $this->belongsTo(InterviewRound::class, 'interview_round_id');
    }

    public function interviewerEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'interviewer_employee_id');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(InterviewEvaluationScore::class, 'interview_evaluation_id');
    }

    public function getRecommendationBadgeClassAttribute(): string
    {
        return match ($this->overall_recommendation) {
            'strongly_recommend' => 'success',
            'recommend'          => 'primary',
            'hold'               => 'warning',
            'not_recommend'      => 'danger',
            default              => 'default',
        };
    }

    public function getRecommendationLabelAttribute(): string
    {
        return match ($this->overall_recommendation) {
            'strongly_recommend' => 'Strongly Recommend',
            'recommend'          => 'Recommend',
            'hold'               => 'Hold',
            'not_recommend'      => 'Not Recommend',
            default              => ucfirst(str_replace('_', ' ', (string) $this->overall_recommendation)),
        };
    }

    public function averageScore(): ?float
    {
        $avg = $this->scores()->avg('score');
        return $avg !== null ? round((float) $avg, 2) : null;
    }
}

