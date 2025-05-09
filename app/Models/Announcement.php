<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['pivot', 'category_id', 'user_id'];
    public $casts = [
        'all_employees' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(AnnouncementCategory::class);
    }
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'annoucement_branches')->withTimestamps();
    }
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'announcement_organizations')->withTimestamps();
    }
    public function positions()
    {
        return $this->belongsToMany(Position::class, 'announcement_positions')->withTimestamps();
    }
    public function levels()
    {
        return $this->belongsToMany(JobLevel::class, 'announcement_job_levels')->withTimestamps();
    }
}
