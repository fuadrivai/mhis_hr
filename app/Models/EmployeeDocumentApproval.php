<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocumentApproval extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $casts = [
        'employee_document_id' => 'integer',
        'approved_by'          => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(EmployeeDocument::class, 'employee_document_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
