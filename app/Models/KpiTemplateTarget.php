<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiTemplateTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'target_score',
        'weight',
    ];

    public function template()
    {
        return $this->belongsTo(KpiTemplate::class, 'kpi_template_id');
    }

    public function subTargets()
    {
        return $this->hasMany(KpiTemplateSubTarget::class);
    }
}
