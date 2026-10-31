<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvMatchOverride extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'override_score' => 'integer',
    ];

    public function result(): BelongsTo
    {
        return $this->belongsTo(CvMatchResult::class, 'cv_match_result_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }
}

