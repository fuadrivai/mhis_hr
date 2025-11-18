<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeOff extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'need_attachment' => 'boolean',
        'is_paid' => 'boolean',
        'deduct_from_leave' => 'boolean',
    ];
}
