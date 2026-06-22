<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reprimand extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reprimand_type_id',
        'effective_date',
        'end_date',
        'notes',
        'attachment_link',
        'document_template_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reprimandType()
    {
        return $this->belongsTo(ReprimandType::class);
    }

    public function watchers()
    {
        return $this->belongsToMany(Employee::class, 'reprimand_watchers', 'reprimand_id', 'employee_id');
    }
}
