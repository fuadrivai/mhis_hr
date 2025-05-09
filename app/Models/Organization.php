<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['pivot'];

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'annoucement_branches')->withTimestamps();
    }
}
