<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['requester_employee_id','approval_rule_id','timeoff_id','approval_rule_id'];

    public function approval_rule()
    {
        return $this->belongsTo(ApprovalRule::class);
    }

    public function data()
    {
        return $this->hasOne(ApprovalRequestData::class);
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requester_employee_id');
    }

    public function attachments()
    {
        return $this->hasMany(ApprovalRequestAttachment::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    public function type()
    {
        return $this->belongsTo(TimeOff::class, 'timeoff_id');
    }

    public function histories()
    {
        return $this->hasMany(ApprovalHistory::class);
    }
}
