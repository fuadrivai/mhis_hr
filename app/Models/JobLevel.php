<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobLevel extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['pivot'];

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_job_levels')->withTimestamps();
    }
}
