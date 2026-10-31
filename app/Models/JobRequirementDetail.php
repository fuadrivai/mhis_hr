<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class JobRequirementDetail extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'weight'       => 'integer',
    ];

    public function jobRequirement(): BelongsTo
    {
        return $this->belongsTo(JobRequirement::class);
    }
}
