<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectCategoryApprover extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}


