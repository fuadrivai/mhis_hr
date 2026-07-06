<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentSubmission extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function assignment()
    {
        return $this->belongsTo(AssessmentAssignment::class, 'assessment_assignment_id');
    }

    public function target()
    {
        return $this->belongsTo(AssessmentTarget::class, 'assessment_target_id');
    }

    public function approvals()
    {
        return $this->hasMany(AssessmentApproval::class);
    }
}
