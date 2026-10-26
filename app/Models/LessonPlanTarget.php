<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPlanTarget extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function months()
    {
        return $this->hasMany(LessonPlanTargetMonth::class);
    }

    public function subjectCategories()
    {
        return $this->belongsToMany(SubjectCategory::class, 'lesson_plan_target_subject_categories');
    }

    public function appliesToCategory($categoryId)
    {
        if ($this->subjectCategories->isEmpty()) {
            return true;
        }
        return $this->subjectCategories->contains('id', $categoryId);
    }
}
