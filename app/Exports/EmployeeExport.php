<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class EmployeeExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithCustomValueBinder
{
    private const IDENTITY_NUMBER_COLUMN = 'Q';

    private Collection $employees;

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
        return \App\Imports\EmployeeImport::templateHeadings();
    }

    public function map($employee): array
    {
        $personal = $employee->personal;
        $employment = $employee->employment;
        $identityNumber = optional($personal)->identity_number;

        return [
            optional($employment)->employee_id,
            optional($personal)->fullname,
            optional($employment)->barcode ?? optional($personal)->barcode,
            optional(optional($employment)->organization)->name,
            optional(optional($employment)->job_position)->name,
            optional(optional($employment)->job_level)->name,
            $this->formatDate(optional($employment)->join_date),
            $this->formatDate(optional($employment)->resign_date),
            optional($employment)->employment_status,
            $this->formatDate(optional($employment)->end_date),
            $this->formatDate(optional($employment)->sign_date),
            optional($personal)->email,
            $this->formatDate(optional($personal)->birth_date),
            optional($personal)->birth_place,
            optional($personal)->address,
            optional($personal)->current_address,
            $identityNumber === null ? null : (string) $identityNumber,
            optional($personal)->mobile_phone,
            optional($personal)->phone,
            optional(optional($employment)->branch)->name,
            optional(optional($personal)->religion)->name,
            optional($personal)->gendre === null
                ? null
                : (optional($personal)->gendre == 1 ? 'Male' : 'Female'),
            $this->maritalStatus(optional($personal)->marital_status),
            optional($personal)->blood_type,
            optional($employment)->nationality_code,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === self::IDENTITY_NUMBER_COLUMN && $value !== null) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    private function formatDate($value): ?string
    {
        return $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : null;
    }

    private function maritalStatus($value): ?string
    {
        switch ((string) $value) {
            case '1':
                return 'Single';
            case '2':
                return 'Married';
            case '3':
                return 'Widow';
            case '4':
                return 'Widower';
            default:
                return null;
        }
    }
}
