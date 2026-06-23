<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function subjectCategory()
    {
        return $this->belongsTo(SubjectCategory::class);
    }

    public function employeeSubjects()
    {
        return $this->hasMany(EmployeeSubject::class);
    }
}
