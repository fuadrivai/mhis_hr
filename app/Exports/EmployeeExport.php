<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping
{
    private Collection $employees;
    private int $number = 0;

    public function __construct(Collection $employees)
    {
        $this->employees = $employees;
    }

    public function collection(): Collection
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            'No',
            'Full Name',
            'Employee ID',
            'Email',
            'Mobile Phone',
            'Place of Birth',
            'D.O.B',
            'Gender',
            'NIK',
            'Branch',
            'Organization',
            'Job Position',
            'Job Level',
            'Employment Status',
            'Join Date',
            'End Date',
            'Active Status',
        ];
    }

    public function map($employee): array
    {
        return [
            ++$this->number,
            optional($employee->personal)->fullname,
            optional($employee->employment)->employee_id,
            optional($employee->personal)->email,
            optional($employee->personal)->mobile_phone,
            optional($employee->personal)->birth_place,
            optional($employee->personal)->birth_date,
            optional($employee->personal)->gendre == 1 ? 'Male' : 'Female',
            optional($employee->personal)->identity_number,
            optional($employee->employment)->branch_name,
            optional($employee->employment)->organization_name,
            optional($employee->employment)->job_position_name,
            optional($employee->employment)->job_level_name,
            optional($employee->employment)->employment_status,
            optional($employee->employment)->join_date,
            optional($employee->employment)->end_date,
            optional($employee)->is_active == 1 ? 'Active' : 'Inactive',
        ];
    }
}
