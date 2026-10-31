<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobRequirement extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(JobRequirementDetail::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(JobRequirementPosition::class);
    }

    public function openings(): HasMany
    {
        return $this->hasMany(JobOpening::class);
    }
}
