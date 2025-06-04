<?php

namespace Database\Seeders;

use App\Models\Relationship;
use Illuminate\Database\Seeder;

class RelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Relationship::create([
            'name' => "Father",
        ]);
        Relationship::create([
            'name' => "Mother",
        ]);
        Relationship::create([
            'name' => "Sibling",
        ]);
        Relationship::create([
            'name' => "Spouse",
        ]);
        Relationship::create([
            'name' => "Child",
        ]);
        Relationship::create([
            'name' => "Cousin",
        ]);
        Relationship::create([
            'name' => "Nibling",
        ]);
        Relationship::create([
            'name' => "Parent In Law",
        ]);
        Relationship::create([
            'name' => "Brother In Law",
        ]);
        Relationship::create([
            'name' => "Sister In Law",
        ]);
        Relationship::create([
            'name' => "Uncle",
        ]);
        Relationship::create([
            'name' => "Aunt",
        ]);
        Relationship::create([
            'name' => "Grandfather",
        ]);
        Relationship::create([
            'name' => "Grandmother",
        ]);
        Relationship::create([
            'name' => "Friend",
        ]);
        Relationship::create([
            'name' => "Coworker",
        ]);
        Relationship::create([
            'name' => "Others",
        ]);
    }
}
