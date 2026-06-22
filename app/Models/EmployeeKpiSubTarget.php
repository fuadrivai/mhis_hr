<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeKpiSubTarget extends Model
{
    use HasFactory;

    protected $fillable = ['employee_kpi_target_id', 'name', 'target_score', 'weight'];

    public function target()
    {
        return $this->belongsTo(EmployeeKpiTarget::class, 'employee_kpi_target_id');
    }
}
