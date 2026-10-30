<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EmployeeImportTemplateTest extends TestCase
{
    public function test_employee_import_template_can_be_downloaded(): void
    {
        $response = $this->withoutMiddleware()->get('/employee/import-template');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = substr($response->streamedContent(), 3);
        $headings = str_getcsv(trim($content));

        $this->assertSame('Employee ID', $headings[0]);
        $this->assertSame('Full Name', $headings[1]);
        $this->assertSame('Blood Type', $headings[23]);
        $this->assertSame('Nationality Code', $headings[24]);
    }

    public function test_invalid_employee_rows_return_validation_errors(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'employees.csv',
            "Employee ID,Full Name\n123,Example Employee\n"
        );

        $response = $this->withoutMiddleware()->post('/employee/import', [
            'file' => $file,
        ]);

        $response->assertRedirect('/employee');
        $response->assertSessionHasErrors('file');
    }
}
