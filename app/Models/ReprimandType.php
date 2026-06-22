<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReprimandType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'level', 'deduction_score'];

    public function reprimands()
    {
        return $this->hasMany(Reprimand::class);
    }
}
