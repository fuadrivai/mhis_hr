<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPlanApproval extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function lessonPlanSubmission()
    {
        return $this->belongsTo(LessonPlanSubmission::class);
    }

    public function approverEmployee()
    {
        return $this->belongsTo(Employee::class, 'approver_employee_id');
    }
}
