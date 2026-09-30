<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTimeoffBalance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'allocated_hours' => 'decimal:2',
        'used_hours' => 'decimal:2',
        'remaining_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function balanceGroup()
    {
        return $this->belongsTo(TimeoffBalanceGroup::class, 'balance_group_id');
    }

    public function transactions()
    {
        return $this->hasMany(EmployeeTimeoffBalanceTransaction::class, 'balance_id');
    }
}
