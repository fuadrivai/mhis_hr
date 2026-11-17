<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
    public function position()
    {
        return $this->belongsTo(Position::class, 'job_position_id');
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

    public function scopeForLengthOfService(Builder $query, string $range): Builder
    {
        if ($range === 'all') {
            return $query;
        }

        $today = now()->startOfDay();
        $oneYearAgo = $today->copy()->subYear()->toDateString();
        $fourYearsAgo = $today->copy()->subYears(4)->toDateString();
        $sixYearsAgo = $today->copy()->subYears(6)->toDateString();
        $elevenYearsAgo = $today->copy()->subYears(11)->toDateString();

        $query->whereNotNull('join_date')
            ->whereDate('join_date', '<=', $today->toDateString());

        switch ($range) {
            case '0_to_5':
                return $query->whereDate('join_date', '>', $sixYearsAgo);
            case 'less_than_1':
                return $query->whereDate('join_date', '>', $oneYearAgo);
            case '1_to_3':
                return $query->whereDate('join_date', '<=', $oneYearAgo)
                    ->whereDate('join_date', '>', $fourYearsAgo);
            case '3_to_5':
                return $query->whereDate('join_date', '<=', $fourYearsAgo)
                    ->whereDate('join_date', '>', $sixYearsAgo);
            case '5_to_10':
                return $query->whereDate('join_date', '<=', $sixYearsAgo)
                    ->whereDate('join_date', '>', $elevenYearsAgo);
            case 'more_than_10':
                return $query->whereDate('join_date', '<=', $elevenYearsAgo);
            default:
                throw new \InvalidArgumentException("Invalid length-of-service range: {$range}");
        }
    }

    public function timeoffs()
    {
        return $this->belongsToMany(
            TimeOff::class,
            'employee_timeoffs',
            'employee_id',
            'timeoff_id'
        );
    }

    public function leaveAllocations()
    {
        return $this->hasMany(LeaveAllocation::class);
    }
}
