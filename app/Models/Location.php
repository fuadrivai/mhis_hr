<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        "need_location" => "boolean"
    ];

    public function details()
    {
        return $this->hasMany(LocationDetail::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function detailsCount()
    {
        return $this->details()->count();
    }
    public function employeesCount()
    {
        return $this->employees()->count();
    }
}
