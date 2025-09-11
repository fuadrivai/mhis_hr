<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['user_id', 'personal_id', 'employment_id', 'pin_location_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function personal()
    {
        return $this->belongsTo(Personal::class);
    }
    public function employment()
    {
        return $this->belongsTo(Employment::class);
    }
    public function pin_location()
    {
        return $this->belongsTo(PinLocation::class);
    }
    public function schedules()
    {
        return $this->hasMany(EmployeeSchedule::class);
    }
    public function activeSchedule()
    {
        return $this->hasOne(EmployeeSchedule::class)
            ->where('effective_start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_end_date')
                    ->orWhere('effective_end_date', '>=', now());
            })
            ->orderByDesc('effective_start_date')
            ->withDefault(); // <= biar gak error kalau null
    }
}
