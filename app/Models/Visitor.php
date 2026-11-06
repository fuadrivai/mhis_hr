<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitor extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function visits(): HasMany
    {
        return $this->hasMany(VisitorVisit::class);
    }
}
