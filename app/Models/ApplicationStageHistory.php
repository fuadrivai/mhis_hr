<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStageHistory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class, 'candidate_application_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

