<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveAttendanceSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'need_face_recognition' => 'boolean',
    ];
}
