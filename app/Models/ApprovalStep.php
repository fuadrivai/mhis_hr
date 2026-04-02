<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['approval_rule_id','approver_position_id','approver_employment_id'];

    public function rule()
    {
        return $this->belongsTo(ApprovalRule::class, 'approval_rule_id');
    }
    public function approverPosition()
    {
        return $this->belongsTo(Position::class, 'approver_position_id');
    }
    public function approverEmployment()
    {
        return $this->belongsTo(Employment::class,'approver_employment_id');
    }
}
