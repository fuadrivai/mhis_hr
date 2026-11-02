<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationAssessment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'assessed_at' => 'date',
    ];

    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class, 'candidate_application_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getConclusionBadgeClassAttribute(): string
    {
        return match ($this->conclusion) {
            'recommended'     => 'success',
            'considered'      => 'warning',
            'not_recommended' => 'danger',
            default           => 'default',
        };
    }

    public function getConclusionLabelAttribute(): string
    {
        return match ($this->conclusion) {
            'recommended'     => 'Recommended',
            'considered'      => 'Considered',
            'not_recommended' => 'Not Recommended',
            default           => ucfirst(str_replace('_', ' ', (string) $this->conclusion)),
        };
    }
}

