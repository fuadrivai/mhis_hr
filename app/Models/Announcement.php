<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['pivot','category_id','created_by','updated_by', 'created_at','updated_at'];

    protected $casts = [
        'publish_at' => 'datetime',
        'all_employees' => 'boolean',
        'send_email' => 'boolean',
        'send_push_notification' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(Employee::class, 'updated_by', 'id');
    }

    public function category()
    {
        return $this->belongsTo(AnnouncementCategory::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'announcement_branches')->withTimestamps();
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'announcement_organizations')->withTimestamps();
    }

    public function jobLevels()
    {
        return $this->belongsToMany(JobLevel::class, 'announcement_job_levels')->withTimestamps();
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'announcement_positions')->withTimestamps();
    }

    public function levels()
    {
        return $this->jobLevels();
    }

    public function reads()
    {
        return $this->hasMany(AnnouncementRead::class);
    }
}
