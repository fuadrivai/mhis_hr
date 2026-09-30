<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTimeoffBalanceTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function balance()
    {
        return $this->belongsTo(EmployeeTimeoffBalance::class, 'balance_id');
    }

    public function timeoff()
    {
        return $this->belongsTo(TimeOff::class, 'timeoff_id');
    }

    public function approvalRequest()
    {
        return $this->belongsTo(ApprovalRequest::class);
    }
}
