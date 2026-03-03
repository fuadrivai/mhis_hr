<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_required' => 'boolean',
        'has_expiry'  => 'boolean',
        'is_visible'  => 'boolean',
    ];

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }
}
