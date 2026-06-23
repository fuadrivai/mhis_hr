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
}
