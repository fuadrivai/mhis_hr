<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DocumentCategory::create([
            'name' => "KTP",
            'is_required' => true,
            'has_expiry' => false,
            'is_visible' => true,
        ]);
        DocumentCategory::create([
            'name' => "Passport",
            'is_required' => true,
            'has_expiry' => true,
            'is_visible' => true,
        ]);
        DocumentCategory::create([
            'name' => "Visa",
            'is_required' => true,
            'has_expiry' => true,
            'is_visible' => true,
        ]);
        DocumentCategory::create([
            'name' => "Driver License",
            'is_required' => false,
            'has_expiry' => true,
            'is_visible' => true,
        ]);
        DocumentCategory::create([
            'name' => "Family Card",
            'is_required' => true,
            'has_expiry' => false,
            'is_visible' => true,
        ]);
        DocumentCategory::create([
            'name' => "Birth Certificate",
            'is_required' => true,
            'has_expiry' => false,
            'is_visible' => true,
        ]);
        DocumentCategory::create([
            'name' => "NPWP",
            'is_required' => true,
            'has_expiry' => false,
            'is_visible' => true,
        ]);
        DocumentCategory::create([
            'name' => "BPJS Kesehatan",
            'is_required' => true,
            'has_expiry' => false,
            'is_visible' => true,
        ]);
        DocumentCategory::create([
            'name' => "BPJS Ketenagakerjaan",
            'is_required' => true,
            'has_expiry' => false,
            'is_visible' => true,
        ]);
        DocumentCategory::create([
            'name' => "Diploma Certificate",
            'is_required' => true,
            'has_expiry' => false,
            'is_visible' => true,
        ]);
        DocumentCategory::create([
            'name' => "Transcript of Records",
            'is_required' => true,
            'has_expiry' => false,
            'is_visible' => true,
        ]);
        DocumentCategory::create([
            'name' => "Other Documents",
            'is_required' => false,
            'has_expiry' => false,
            'is_visible' => true,
        ]);
    }
}
