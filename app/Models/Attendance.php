<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
        'employee_id' => 'integer',
        'user_id' => 'integer',
        'check_in_latitude' => 'float',
        'check_in_longitude' => 'float',
        'check_in_radius' => 'integer',
        'check_out_latitude' => 'float',
        'check_out_longitude' => 'float',
        'check_out_radius' => 'integer',
        'is_locked' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
    public function approvalRequests()
    {
        return $this->belongsToMany(
            ApprovalRequest::class,
            'approval_request_attendances',
            'attendance_id',
            'approval_request_id'
        );
    }
}
