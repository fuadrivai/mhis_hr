<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiTemplate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function targets()
    {
        return $this->hasMany(KpiTemplateTarget::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
