<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSchedule extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['employee_id', 'schedule_id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function effectiveDate()
    {
        return Carbon::parse($this->effective_start_date)->format('d F Y');
    }
}
