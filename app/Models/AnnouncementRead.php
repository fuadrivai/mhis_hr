<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementRead extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'read_at' => 'datetime'
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
