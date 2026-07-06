<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentApproval extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function submission()
    {
        return $this->belongsTo(AssessmentSubmission::class, 'assessment_submission_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}
