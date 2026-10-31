<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvMatchRequirementResult extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_mandatory'      => 'boolean',
        'weight'            => 'integer',
        'evidence_verified' => 'boolean',
    ];

    public function result(): BelongsTo
    {
        return $this->belongsTo(CvMatchResult::class, 'cv_match_result_id');
    }

    public function detail(): BelongsTo
    {
        return $this->belongsTo(JobRequirementDetail::class, 'job_requirement_detail_id');
    }
}

