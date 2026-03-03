<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocumentVersion extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'employee_document_id' => 'integer',
        'file_size'            => 'integer',
        'version'              => 'integer',
        'uploaded_by'          => 'integer',
        'is_latest'            => 'boolean',
    ];

    public function document()
    {
        return $this->belongsTo(EmployeeDocument::class, 'employee_document_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeLatest($query)
    {
        return $query->where('is_latest', true);
    }
}
