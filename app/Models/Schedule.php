<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function effectiveDate()
    {
        $date = $this->effective_date;
        $strDate = null;
        if (isset($date)) {
            $strDate = Carbon::parse($date)->format('d F Y');
        }
        return $strDate;
    }


    public function details()
    {
        return $this->hasMany(ScheduleDetail::class);
    }
}
