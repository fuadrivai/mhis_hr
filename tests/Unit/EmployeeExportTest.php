<?php

namespace Tests\Unit;

use App\Exports\EmployeeExport;
use App\Imports\EmployeeImport;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobLevel;
use App\Models\Organization;
use App\Models\Personal;
use App\Models\Position;
use App\Models\Religion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as BaseExcel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class EmployeeExportTest extends TestCase
{
    public function test_export_headings_include_calculated_columns_without_changing_import_template(): void
    {
        $export = new EmployeeExport(new Collection());

        $headings = EmployeeImport::templateHeadings();
        array_splice($headings, 11, 0, ['Lama Bekerja']);
        array_splice($headings, 15, 0, ['Usia']);

        $this->assertSame($headings, $export->headings());
    }

    public function test_export_rows_follow_the_export_column_order(): void
    {
        $personal = (new Personal())->forceFill([
            'fullname' => 'Example Employee',
            'barcode' => 'B-001',
            'email' => 'employee@example.com',
            'birth_date' => '1990-02-03',
            'birth_place' => 'Jakarta',
            'address' => 'Home address',
            'current_address' => 'Current address',
            'identity_number' => '001234',
            'mobile_phone' => '08123456789',
            'phone' => '021123456',
            'gendre' => '2',
            'marital_status' => '4',
            'blood_type' => 'AB',
        ]);
        $personal->setRelation('religion', (new Religion())->forceFill(['name' => 'Islam']));

        $employment = (new Employment())->forceFill([
            'employee_id' => 'EMP-001',
            'barcode' => 'B-001',
            'join_date' => '2020-01-02',
            'resign_date' => null,
            'employment_status' => 'permanent',
            'end_date' => null,
            'sign_date' => '2020-01-01',
            'nationality_code' => 'ID',
        ]);
        $employment->setRelation('organization', (new Organization())->forceFill(['name' => 'HR']));
        $employment->setRelation('job_position', (new Position())->forceFill(['name' => 'Manager']));
        $employment->setRelation('job_level', (new JobLevel())->forceFill(['name' => 'L3']));
        $employment->setRelation('branch', (new Branch())->forceFill(['name' => 'Bintaro']));

        $employee = new Employee();
        $employee->setRelation('personal', $personal);
        $employee->setRelation('employment', $employment);

        \Illuminate\Support\Carbon::setTestNow('2026-11-17');

        try {
            $export = new EmployeeExport(new Collection([$employee]));

            $this->assertSame([
                'EMP-001',
                'Example Employee',
                'B-001',
                'HR',
                'Manager',
                'L3',
                '2020-01-02',
                null,
                'permanent',
                null,
                '2020-01-01',
                '6 years 10 months',
                'employee@example.com',
                '1990-02-03',
                'Jakarta',
                '36 years 9 months',
                'Home address',
                'Current address',
                '001234',
                '08123456789',
                '021123456',
                'Bintaro',
                'Islam',
                'Female',
                'Widower',
                'AB',
                'ID',
            ], $export->map($employee));
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }
    }

    public function test_export_preserves_full_identity_number_as_text(): void
    {
        $identityNumber = '1234567890123456';
        $personal = (new Personal())->forceFill(['identity_number' => $identityNumber]);
        $personal->setRelation('religion', null);

        $employment = new Employment();
        $employment->setRelation('organization', null);
        $employment->setRelation('job_position', null);
        $employment->setRelation('job_level', null);
        $employment->setRelation('branch', null);

        $employee = new Employee();
        $employee->setRelation('personal', $personal);
        $employee->setRelation('employment', $employment);

        $contents = Excel::raw(
            new EmployeeExport(new Collection([$employee])),
            BaseExcel::XLSX
        );
        $tempFile = tempnam(sys_get_temp_dir(), 'employee-export-');

        $this->assertNotFalse($tempFile);

        try {
            file_put_contents($tempFile, $contents);
            $spreadsheet = IOFactory::load($tempFile);
            $sheet = $spreadsheet->getActiveSheet();
            $cell = $sheet->getCell('S2');

            $this->assertSame($identityNumber, $cell->getValue());
            $this->assertSame(DataType::TYPE_STRING, $cell->getDataType());
            $this->assertNull($sheet->getCell('L2')->getValue());
            $this->assertNull($sheet->getCell('P2')->getValue());
        } finally {
            if (is_file($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
