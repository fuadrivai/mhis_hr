<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $with = ['religion'];
    protected $hidden = ['religion_id'];

    public function religion()
    {
        return $this->belongsTo(Religion::class);
    }
    public function families()
    {
        return $this->hasMany(Family::class);
    }
    public function emergency_contacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }
}
