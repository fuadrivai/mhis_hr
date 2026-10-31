<?php

namespace Tests\Feature;

use App\Jobs\ProcessCvMatchJob;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\JobOpening;
use App\Models\JobRequirement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecruitmentBulkCvTest extends TestCase
{
    private ?User $admin = null;
    private array $cleanupOpeningIds = [];
    private array $cleanupCandidateIds = [];
    private array $cleanupRequirementIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::find(1);
    }

    protected function tearDown(): void
    {
        if (!empty($this->cleanupCandidateIds)) {
            $candidates = Candidate::whereIn('id', $this->cleanupCandidateIds)->get();
            foreach ($candidates as $cand) {
                foreach ($cand->applications as $app) {
                    if ($app->resume_path) {
                        Storage::disk('local')->delete($app->resume_path);
                    }
                    $app->stageHistories()->delete();
                    $app->cvMatchResults()->delete();
                    $app->delete();
                }
                $cand->delete();
            }
        }

        if (!empty($this->cleanupOpeningIds)) {
            JobOpening::whereIn('id', $this->cleanupOpeningIds)->delete();
        }

        if (!empty($this->cleanupRequirementIds)) {
            $reqs = JobRequirement::whereIn('id', $this->cleanupRequirementIds)->get();
            foreach ($reqs as $r) {
                $r->details()->delete();
                $r->delete();
            }
        }

        parent::tearDown();
    }

    public function test_candidate_form_renders_bulk_cv_tab(): void
    {
        $response = $this->actingAs($this->admin)->get(route('recruitment.candidates.form'));

        $response->assertStatus(200);
        $response->assertSee('Bulk CV Upload');
        $response->assertSee('bulkCvDropzone');
        $response->assertSee('btnStartBulkUpload');
    }

    public function test_candidate_form_accepts_bulk_tab_query_param(): void
    {
        $response = $this->actingAs($this->admin)->get(route('recruitment.candidates.form', ['tab' => 'bulk_cv']));

        $response->assertStatus(200);
        $response->assertSee('id="bulkCvUpload"', false);
    }

    public function test_bulk_upload_item_registers_candidate_and_dispatches_matching(): void
    {
        Queue::fake();

        $branch = Branch::first();
        $req = JobRequirement::create([
            'title' => 'Bulk Test Requirement ' . uniqid(),
            'description' => 'Test requirement',
        ]);
        $this->cleanupRequirementIds[] = $req->id;

        $opening = JobOpening::create([
            'title' => 'Bulk Science Teacher ' . uniqid(),
            'job_requirement_id' => $req->id,
            'branch_id' => $branch->id,
            'headcount' => 3,
            'status' => 'open',
            'opened_at' => now(),
            'created_by' => $this->admin->id,
        ]);
        $this->cleanupOpeningIds[] = $opening->id;

        $cvText = <<<CV
Bambang Pamungkas
Email: bambang.bulk.test@example.com
Phone: +62 812 3344 5566
Location: Jakarta Pusat, Indonesia

EXPERIENCE
Secondary Science Teacher for 5 years
CV;

        $file = UploadedFile::fake()->createWithContent('bambang_cv.txt', $cvText);

        $response = $this->actingAs($this->admin)
            ->post(route('recruitment.candidates.bulk-upload-item'), [
                'cv_file'        => $file,
                'job_opening_id' => $opening->id,
                'source'         => 'LinkedIn',
                'source_detail'  => 'October Batch',
                'consent'        => '1',
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'    => 'success',
            'email'     => 'bambang.bulk.test@example.com',
            'full_name' => 'Bambang Pamungkas',
        ]);

        $candidateId = $response->json('candidate_id');
        $this->cleanupCandidateIds[] = $candidateId;

        $candidate = Candidate::find($candidateId);
        $this->assertNotNull($candidate);
        $this->assertEquals('bambang.bulk.test@example.com', $candidate->email);
        $this->assertEquals('Bambang Pamungkas', $candidate->full_name);

        $application = $candidate->applications()->where('job_opening_id', $opening->id)->first();
        $this->assertNotNull($application);
        $this->assertNotNull($application->resume_path);
        Storage::disk('local')->assertExists($application->resume_path);

        Queue::assertPushed(ProcessCvMatchJob::class, function ($job) use ($application) {
            return $job->candidateApplicationId === $application->id;
        });
    }

    public function test_bulk_upload_item_detects_missing_email_and_allows_override(): void
    {
        $branch = Branch::first();
        $opening = JobOpening::create([
            'title' => 'Bulk English Teacher ' . uniqid(),
            'branch_id' => $branch->id,
            'headcount' => 2,
            'status' => 'open',
            'opened_at' => now(),
            'created_by' => $this->admin->id,
        ]);
        $this->cleanupOpeningIds[] = $opening->id;

        // CV without an email address
        $cvText = <<<CV
Dewi Sartika
Phone: 0811223344
City: Bandung
Teaching Experience in Primary English
CV;

        $file = UploadedFile::fake()->createWithContent('dewi_cv.txt', $cvText);

        // 1. First attempt fails with 422 MISSING_EMAIL
        $response = $this->actingAs($this->admin)
            ->post(route('recruitment.candidates.bulk-upload-item'), [
                'cv_file'        => $file,
                'job_opening_id' => $opening->id,
                'source'         => 'LinkedIn',
                'consent'        => '1',
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'code'   => 'MISSING_EMAIL',
        ]);

        // 2. Retry with manually provided email
        $retryResponse = $this->actingAs($this->admin)
            ->post(route('recruitment.candidates.bulk-upload-item'), [
                'cv_file'        => $file,
                'job_opening_id' => $opening->id,
                'source'         => 'LinkedIn',
                'consent'        => '1',
                'email'          => 'dewi.sartika.' . uniqid() . '@example.com',
                'full_name'      => 'Dewi Sartika',
            ], [
                'Accept' => 'application/json',
            ]);

        $retryResponse->assertStatus(200);
        $retryResponse->assertJson([
            'status'    => 'success',
            'full_name' => 'Dewi Sartika',
        ]);

        $candidateId = $retryResponse->json('candidate_id');
        $this->cleanupCandidateIds[] = $candidateId;
    }

    public function test_batch_bulk_upload_endpoint_handles_multiple_files(): void
    {
        $branch = Branch::first();
        $opening = JobOpening::create([
            'title' => 'Multi File Batch ' . uniqid(),
            'branch_id' => $branch->id,
            'headcount' => 5,
            'status' => 'open',
            'opened_at' => now(),
            'created_by' => $this->admin->id,
        ]);
        $this->cleanupOpeningIds[] = $opening->id;

        $cv1 = UploadedFile::fake()->createWithContent('candidate_one.txt', "Candidate One\nEmail: cand.one." . uniqid() . "@example.com\nPhone: 0812345678");
        $cv2 = UploadedFile::fake()->createWithContent('candidate_two.txt', "Candidate Two\nEmail: cand.two." . uniqid() . "@example.com\nPhone: 0812345679");

        $response = $this->actingAs($this->admin)
            ->post(route('recruitment.candidates.bulk-upload'), [
                'cv_files'       => [$cv1, $cv2],
                'job_opening_id' => $opening->id,
                'source'         => 'LinkedIn',
                'consent'        => '1',
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'           => 'success',
            'total'            => 2,
            'successful_count' => 2,
            'failed_count'     => 0,
        ]);

        foreach ($response->json('successful') as $item) {
            $this->cleanupCandidateIds[] = $item['candidate_id'];
        }
    }
}
