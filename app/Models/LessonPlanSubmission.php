<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPlanSubmission extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function employeeSubject()
    {
        return $this->belongsTo(EmployeeSubject::class);
    }

    public function lessonPlanTargetMonth()
    {
        return $this->belongsTo(LessonPlanTargetMonth::class);
    }

    public function approvals()
    {
        return $this->hasMany(LessonPlanApproval::class);
    }
}
