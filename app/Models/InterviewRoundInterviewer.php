<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewRoundInterviewer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function interviewRound(): BelongsTo
    {
        return $this->belongsTo(InterviewRound::class, 'interview_round_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}

