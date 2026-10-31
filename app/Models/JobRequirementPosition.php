<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRequirementPosition extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    public function jobRequirement(): BelongsTo
    {
        return $this->belongsTo(JobRequirement::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}

