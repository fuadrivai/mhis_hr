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

    public function schedule_durations()
    {
        $this->schedule_duration = diffTime($this->schedule_in, $this->schedule_out);
    }
    public function break_durations()
    {
        $this->break_duration = diffTime($this->break_start, $this->break_end);
    }
}
