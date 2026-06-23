<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPlanTargetMonth extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function lessonPlanTarget()
    {
        return $this->belongsTo(LessonPlanTarget::class);
    }

    public function submissions()
    {
        return $this->hasMany(LessonPlanSubmission::class);
    }
}
