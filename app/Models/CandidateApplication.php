<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CandidateApplication extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'consent_at'       => 'datetime',
        'applied_at'       => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class, 'job_opening_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function hrPic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_pic_id');
    }

    public function stageHistories(): HasMany
    {
        return $this->hasMany(ApplicationStageHistory::class, 'candidate_application_id')->orderBy('changed_at', 'desc');
    }

    public function cvMatchResults(): HasMany
    {
        return $this->hasMany(CvMatchResult::class, 'candidate_application_id')->latest();
    }

    public function latestCvMatchResult(): HasOne
    {
        return $this->hasOne(CvMatchResult::class, 'candidate_application_id')->latestOfMany();
    }
}
