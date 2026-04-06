<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['approval_rule_id','approver_employee_id','created_at','updated_at'];

    public function rule()
    {
        return $this->belongsTo(ApprovalRule::class, 'approval_rule_id');
    }
    public function approverEmployee()
    {
        return $this->belongsTo(Employee::class, 'approver_employee_id');
    }
}
