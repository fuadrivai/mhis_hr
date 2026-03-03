<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $casts = [
        'employee_id'            => 'integer',
        'document_category_id'   => 'integer',
        'issued_date'            => 'date',
        'expiry_date'            => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function versions()
    {
        return $this->hasMany(EmployeeDocumentVersion::class);
    }

    public function latestVersion()
    {
        return $this->hasOne(EmployeeDocumentVersion::class)
            ->where('is_latest', true);
    }

    public function approvals()
    {
        return $this->hasMany(EmployeeDocumentApproval::class);
    }

    public function lastApproval()
    {
        return $this->hasOne(EmployeeDocumentApproval::class)->latest();
    }
}
