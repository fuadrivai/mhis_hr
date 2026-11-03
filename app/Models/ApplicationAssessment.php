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

    public function getDocumentDisplayLabelAttribute(): string
    {
        if (!empty($this->document_url)) {
            $url = strtolower($this->document_url);
            if (str_contains($url, 'docs.google.com')) {
                return 'Google Docs';
            }
            if (str_contains($url, 'drive.google.com')) {
                return 'Google Drive';
            }
            if (str_ends_with($url, '.pdf') || str_contains($url, '.pdf?')) {
                return 'PDF Document';
            }
            return 'View Document Link';
        }

        return $this->original_filename ?: 'Psychotest Report';
    }

    public function getDocumentIconClassAttribute(): string
    {
        if (!empty($this->document_url)) {
            $url = strtolower($this->document_url);
            if (str_contains($url, 'docs.google.com')) {
                return 'fa fa-file-text-o';
            }
            if (str_contains($url, 'drive.google.com')) {
                return 'fa fa-cloud';
            }
            if (str_ends_with($url, '.pdf') || str_contains($url, '.pdf?')) {
                return 'fa fa-file-pdf-o';
            }
            return 'fa fa-external-link';
        }

        return 'fa fa-file-pdf-o';
    }
}
