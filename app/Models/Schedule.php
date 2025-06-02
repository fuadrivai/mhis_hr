<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public $casts = [
        'ignore_special_holiday' => 'boolean',
        'ignore_company_holiday' => 'boolean',
        'ignore_national_holiday' => 'boolean',
    ];


    public function details()
    {
        return $this->hasMany(ScheduleDetail::class);
    }
}
