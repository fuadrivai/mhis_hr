<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveAbsent extends Model
{
    use HasFactory;

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
        'distance' => 'float',
        'radius' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function pin_locations()
    {
        return $this->belongsTo(PinLocation::class);
    }
}
