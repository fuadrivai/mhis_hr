<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeKpiTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'target_score',
        'weight',
    ];

    public function kpi()
    {
        return $this->belongsTo(EmployeeKpi::class, 'employee_kpi_id');
    }

    public function subTargets()
    {
        return $this->hasMany(EmployeeKpiSubTarget::class);
    }
}
