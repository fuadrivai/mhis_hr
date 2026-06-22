<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeKpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'academic_year',
        'reprimand_deduction_percentage',
        'managerial_file_url',
        'tal_file_url',
        'final_score',
    ];

    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function targets()
    {
        return $this->hasMany(EmployeeKpiTarget::class);
    }
}
