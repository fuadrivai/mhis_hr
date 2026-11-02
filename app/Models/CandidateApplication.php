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

    public function assessments(): HasMany
    {
        return $this->hasMany(ApplicationAssessment::class, 'candidate_application_id')->orderBy('assessed_at', 'desc');
    }

    public function latestAssessment(): HasOne
    {
        return $this->hasOne(ApplicationAssessment::class, 'candidate_application_id')->latestOfMany('assessed_at');
    }

    public function interviewRounds(): HasMany
    {
        return $this->hasMany(InterviewRound::class, 'candidate_application_id')->orderBy('round_no', 'asc');
    }

    public function latestInterviewRound(): HasOne
    {
        return $this->hasOne(InterviewRound::class, 'candidate_application_id')->latestOfMany('scheduled_at');
    }

    public function rejections(): HasMany
    {
        return $this->hasMany(ApplicationRejection::class, 'candidate_application_id')->latest();
    }

    public function latestRejection(): HasOne
    {
        return $this->hasOne(ApplicationRejection::class, 'candidate_application_id')->latestOfMany();
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'candidate_application_id')->orderBy('version', 'desc')->latest();
    }

    public function latestOffer(): HasOne
    {
        return $this->hasOne(Offer::class, 'candidate_application_id')->latestOfMany('version');
    }

    public function activeOffer(): HasOne
    {
        return $this->hasOne(Offer::class, 'candidate_application_id')
            ->whereNotIn('status', ['cancelled'])
            ->latestOfMany('version');
    }

    public function onboarding(): HasOne
    {
        return $this->hasOne(ApplicationOnboarding::class, 'candidate_application_id');
    }
}
