<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveAllocation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'total' => 'integer',
        'used' => 'integer',
        'remaining' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(
            Employee::class
        );
    }

    public function timeoff()
    {
        return $this->belongsTo(
            TimeOff::class
        );
    }

    public function academicYear()
    {
        return $this->belongsTo(
            AcademicYear::class
        );
    }

    public function histories()
    {
        return $this->hasMany(
            LeaveAllocationHistory::class
        );
    }
}
