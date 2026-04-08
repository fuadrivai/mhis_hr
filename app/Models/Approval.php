<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['approval_request_id','approver_employee_id','approval_request_id'];

    public function approvalRequestData()
    {
        return $this->hasMany(ApprovalRequestData::class, 'approval_request_id');
    }

    public function approvalRequest()
    {
        return $this->belongsTo(ApprovalRequest::class);
    }
    public function approvalHistories()
    {
        return $this->hasMany(ApprovalHistory::class);
    }
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_employee_id');
    }
}
