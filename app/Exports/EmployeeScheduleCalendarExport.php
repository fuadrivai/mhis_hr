<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeScheduleCalendarExport implements FromCollection, WithHeadings, WithMapping
{
    private Collection $rows;
    private array $headings;
    private int $number = 0;

    public function __construct(Collection $rows, array $headings)
    {
        $this->rows = $rows;
        $this->headings = $headings;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return array_merge([
            ++$this->number,
            $row['employee_name'],
            $row['employee_code'],
        ], $row['days']);
    }
}
