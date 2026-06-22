<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiTemplateSubTarget extends Model
{
    use HasFactory;

    protected $fillable = ['kpi_template_target_id', 'name', 'target_score', 'weight'];

    public function target()
    {
        return $this->belongsTo(KpiTemplateTarget::class, 'kpi_template_target_id');
    }
}
