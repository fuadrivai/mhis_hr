<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
        'employee_id' => 'integer',
        'user_id' => 'integer',
        'check_in_latitude' => 'float',
        'check_in_longitude' => 'float',
        'check_in_radius' => 'float',
        'check_out_latitude' => 'float',
        'check_out_longitude' => 'float',
        'check_out_radius' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
