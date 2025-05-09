<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['pivot'];

    public $casts = [
        'is_active' => 'boolean',
    ];

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'annoucement_branches')->withTimestamps();
    }
}
