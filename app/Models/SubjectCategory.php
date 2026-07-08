<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectCategory extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }


    public function monitors()
    {
        return $this->hasMany(SubjectCategoryMonitor::class);
    }
}
