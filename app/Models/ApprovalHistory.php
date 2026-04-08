<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalHistory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['approver_employee_id','approval_request_id','approval_id'];

    public function approver()
    {
        return $this->hasOne(Employee::class, 'id', 'approver_employee_id');
    }
}
