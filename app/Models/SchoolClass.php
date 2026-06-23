<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function employeeSubjects()
    {
        return $this->hasMany(EmployeeSubject::class);
    }
}
