<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $with = ['branch', 'job_level', 'organization', 'job_position'];
    protected $hidden = ['branch_id', 'job_level_id', 'organization_id', 'job_position_id'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function job_position()
    {
        return $this->belongsTo(Position::class);
    }
    public function job_level()
    {
        return $this->belongsTo(JobLevel::class);
    }

    public function joinDate()
    {
        $joinDate = Carbon::parse($this->join_date);
        return $joinDate->format('d F Y');
    }
    public function endDate()
    {
        $endDate = Carbon::parse($this->end_date);
        return $endDate->format('d F Y');
    }

    public function age()
    {
        $age = Carbon::parse($this->join_date)->diff(Carbon::now());
        return "$age->y years $age->m months";
    }
}
