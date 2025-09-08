<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function birthDate()
    {
        $birthDate = Carbon::parse($this->birth_date);
        return $birthDate->format('d F Y');
    }
    public function age()
    {
        $age = Carbon::parse($this->birth_date)->diff(Carbon::now());
        return "$age->y years $age->m months";
    }

    public function expiredIdentity()
    {
        $date = $this->expired_date_identity_id;
        $strDate = null;
        if (isset($date)) {
            $strDate = Carbon::parse($date)->format('d F Y');
        }
        return $strDate;
    }

    public function maritalStatus()
    {
        $status = "";
        switch ($this->marital_status) {
            case 1:
                $status = "Single";
                break;
            case 2:
                $status = "Merried";
                break;
            case 3:
                $status = "Widow";
                break;
            default;
                $status = "Widower";
        }

        return $status;
    }
    public function gender()
    {
        $status = "";
        switch ($this->gendre) {
            case 1:
                $status = "male";
                break;
            case 2:
                $status = "female";
                break;
            default;
                $status = "male";
        }

        return $status;
    }
}
