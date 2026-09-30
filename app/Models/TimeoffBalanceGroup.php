<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeoffBalanceGroup extends Model
{
    use HasFactory;

    protected $table = 'timeoff_balance_groups';
    protected $guarded = ['id'];

    protected $casts = [
        'maximum_hours' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function timeoffs()
    {
        return $this->hasMany(TimeOff::class, 'balance_group_id');
    }

    public function employeeBalances()
    {
        return $this->hasMany(EmployeeTimeoffBalance::class, 'balance_group_id');
    }
}
