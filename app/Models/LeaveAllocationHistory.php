<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveAllocationHistory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'days' => 'integer',
    ];

    public function allocation()
    {
        return $this->belongsTo(
            LeaveAllocation::class,
            'leave_allocation_id'
        );
    }
}
