<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $with = ['relationship'];
    
    public function relationship()
    {
        return $this->belongsTo(Relationship::class,'relation_ship_id');
    }
}
