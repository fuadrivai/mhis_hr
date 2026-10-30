<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateApplication extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'consent_at' => 'datetime',
        'applied_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function hrPic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_pic_id');
    }
}
