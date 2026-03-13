<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $with = ['religion', 'relationship'];

    public function relationship()
    {
        return $this->belongsTo(Relationship::class,'relation_ship_id');
    }
    public function religion()
    {
        return $this->belongsTo(Religion::class);
    }

    public function strGendre(){
        return $this->gendre == 1? "male":"female";
    }
    public function birthDate(){
        return Carbon::parse($this->birth_date)->format('d F Y');
    }
    public function maritalStatus(){
        $status = "";
        switch ($this->marital_status) {
            case '1':
                $status = "Single";
                break;
            case '2':
                $status = "Merried";
                break;
            case '3':
                $status = "Widow";
                break;
            case '4':
                $status = "Widower";
                break;
            
            default:
            $status = "-";
                break;
        }
        return $status;
    }
}
