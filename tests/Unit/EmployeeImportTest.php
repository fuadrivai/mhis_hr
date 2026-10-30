<?php

namespace Tests\Unit;

use App\Imports\EmployeeImport;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Tests\TestCase;

class EmployeeImportTest extends TestCase
{
    public function test_template_headings_match_import_fields(): void
    {
        $headings = HeadingRowFormatter::format(EmployeeImport::templateHeadings());

        $this->assertSame(array_keys((new EmployeeImport())->rules()), array_values($headings));
    }

    public function test_import_normalizes_values_from_the_template(): void
    {
        $import = new EmployeeImport();

        $row = $import->prepareForValidation([
            'employee_id' => 123,
            'full_name' => '  Example Employee  ',
            'organization' => 'Example Organization',
            'job_position' => 'Example Position',
            'job_level' => 'Example Level',
            'employment_status' => 'PERMANENT',
            'email' => 'employee@example.com',
            'branch' => 'Bintaro',
            'religion' => 'Example Religion',
            'gender' => 'female',
            'marital_status' => 'widower',
            'blood_type' => 'ab',
            'join_date' => '2025-01-02',
        ], 2);

        $this->assertSame('123', $row['employee_id']);
        $this->assertSame('Example Employee', $row['full_name']);
        $this->assertSame('permanent', $row['employment_status']);
        $this->assertSame('Female', $row['gender']);
        $this->assertSame('Widower', $row['marital_status']);
        $this->assertSame('AB', $row['blood_type']);
        $this->assertSame('2025-01-02', $row['join_date']);
        $this->assertTrue(Validator::make($row, $import->rules())->passes());
    }
}
