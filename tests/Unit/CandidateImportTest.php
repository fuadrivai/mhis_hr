<?php

namespace Tests\Unit;

use App\Imports\CandidateImport;
use App\Services\CandidateRegistrationService;
use App\Services\Implement\CandidateRegistrationImplement;
use App\Services\Implement\JobRequirementImplement;
use App\Services\JobRequirementService;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Tests\TestCase;

class CandidateImportTest extends TestCase
{
    public function test_candidate_registration_service_is_bound_to_its_implementation(): void
    {
        $this->assertInstanceOf(
            CandidateRegistrationImplement::class,
            $this->app->make(CandidateRegistrationService::class)
        );
    }

    public function test_job_requirement_service_is_bound_to_its_implementation(): void
    {
        $this->assertInstanceOf(
            JobRequirementImplement::class,
            $this->app->make(JobRequirementService::class)
        );
    }

    public function test_template_headings_match_candidate_import_fields(): void
    {
        $headings = HeadingRowFormatter::format(CandidateImport::templateHeadings());

        $this->assertSame([
            'full_name',
            'email',
            'phone_number',
            'current_city',
            'position_applied',
            'branch_preference',
            'application_source',
            'source_detail',
            'consent',
        ], array_values($headings));
    }

    public function test_candidate_csv_rows_are_mapped_by_heading(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'candidates.csv',
            "Full Name,Email,Phone Number,Current City,Position Applied,Branch Preference,Application Source,Source Detail,Consent\n"
                . "Example Candidate,candidate@example.com,08123456789,Jakarta,Teacher,Bintaro,LinkedIn,Campaign 2026,yes\n"
        );

        $rows = Excel::toArray(new CandidateImport(), $file)[0];

        $this->assertSame('Example Candidate', $rows[0]['full_name']);
        $this->assertSame('candidate@example.com', $rows[0]['email']);
        $this->assertSame('Teacher', $rows[0]['position_applied']);
        $this->assertSame('Bintaro', $rows[0]['branch_preference']);
        $this->assertSame('yes', $rows[0]['consent']);
    }
}
