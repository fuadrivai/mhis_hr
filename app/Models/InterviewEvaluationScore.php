<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewEvaluationScore extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'score' => 'integer',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(InterviewEvaluation::class, 'interview_evaluation_id');
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(EvaluationCompetency::class, 'competency_id');
    }
}

