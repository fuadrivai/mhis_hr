<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeOff extends Model
{
    use HasFactory;
    protected $table = 'timeoffs';
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_global' => 'boolean',
        'deduct_leave_balance' => 'boolean',
        'schema' => 'array'
    ];
}
