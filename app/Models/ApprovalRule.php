<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $hidden = ['branch_id','organization_id','job_level_id','position_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function level()
    {
        return $this->belongsTo(JobLevel::class,'job_level_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function steps()
    {
        return $this->hasMany(ApprovalStep::class, 'approval_rule_id');
    }

    public function getFormattedStepsAttribute()
    {
        return collect($this->steps)
            ->groupBy('step_order')
            ->map(function ($steps, $stepOrder) {
                $first = $steps->first();
                return [
                    'step_order' => (int) $stepOrder,
                    'name' => $first->name,
                    'approval_mode' => $first->approval_mode,
                    'approvers' => $steps->map(function ($step) {
                        $employee = $step->approverEmployee;
                        if (!$employee) {
                            return null;
                        }
                        return [
                            'employeeId' => $employee->id,
                            'employeeName' => optional($employee->personal)->fullname,
                        ];
                    })->filter()->values(),
                ];
            })
            ->sortBy('step_order')
            ->values();
    }
}
