<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentApprover extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function subjectCategory()
    {
        return $this->belongsTo(SubjectCategory::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function schoolClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'assessment_approver_school_classes', 'assessment_approver_id', 'school_class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'assessment_approver_subjects', 'assessment_approver_id', 'subject_id');
    }
}
