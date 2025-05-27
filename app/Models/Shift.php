<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function App\Helpers\diffTime;

class Shift extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    // protected $appends = ['schedule_duration', 'break_duration'];
    public $casts = [
        'show_in_request' => 'boolean',
    ];

    public function schedule_duration()
    {
       return diffTime($this->schedule_in, $this->schedule_out);
    }
    public function break_duration()
    {
        return diffTime($this->break_start, $this->break_end);
    }
}
