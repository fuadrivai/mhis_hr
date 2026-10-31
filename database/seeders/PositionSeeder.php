<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            1 => 'Junior High Principal', 2 => 'Kindergarten & Primary Principal', 3 => 'Primary Vice Principal',
            4 => 'Kindergarten Vice Principal', 5 => 'Kindergarten Teacher', 6 => 'Primary Teacher',
            7 => 'Finance Staff', 8 => 'Kindergaten Administrator', 9 => 'HR Staff',
            10 => 'School Admission Staff', 11 => 'Junior High Teacher', 12 => 'Junior High Administrator',
            13 => 'IT Support', 14 => 'Primary Administrator', 15 => 'KIndergarten Teacher Assistant',
            16 => 'GA Staff', 17 => 'Preschool Principal', 18 => 'Preschool Vice Principal',
            19 => 'Preschool Teacher', 20 => 'Preschool Administrator', 21 => 'Preschool Teacher Assistant',
            22 => 'Primary Principal', 23 => 'Primary Vice Principal of Students Affair', 24 => 'Primary Vice Principal of Academic',
            25 => 'Secondary Vice Principal of Students Affair', 26 => 'Secondary Principal', 27 => 'Secondary Vice Principal of Academic',
            28 => 'Secondary Teacher', 29 => 'Secondary Administrator', 30 => 'Head of Development Class',
            31 => 'Development Class Teacher', 32 => 'Development Class Administrator', 33 => 'Director',
            34 => 'Chief of Teacher and Staff Development', 35 => 'Chief of System Development',
            36 => 'Chief of Admission and Communication Development', 37 => 'Chief of Curricula and Program Development',
            38 => 'Head of School Bintaro', 39 => 'General Administrator', 40 => 'Recruitment Staff',
            41 => 'IT Staff', 42 => 'Librarian', 43 => 'Executive Secretary',
            44 => 'HR Senior Staff', 45 => 'Nurse', 46 => 'Social Media & Communication Officer',
            47 => 'Legal Staff', 48 => 'IT Development Staff', 49 => 'Secretariat Staff',
            50 => 'Security', 51 => 'Part Time English Teacher', 52 => 'Part Time Guidance & Counselor',
            53 => 'Part Time Music Teacher', 54 => 'Marketing Officer', 55 => 'HOS Secretary',
            56 => 'Curriculum Specialist Staff', 57 => 'Part Time Business and Enterprise Teacher',
            58 => 'Chief of Secretariat', 59 => 'Special Education Teacher Assistant', 60 => 'Office Girl',
            61 => 'Office Boy', 62 => 'Kindergarten Principal', 63 => 'Recruitment Staff/ HRBP',
            64 => 'HR Lead', 65 => 'Admission Lead', 66 => 'Primary and Junior High Principal',
            67 => 'Primary Vice Principal of Operations & Student Well-Being',
            68 => 'Primary Vice Principal of Academic Excellence', 69 => 'Junior High Vice Principal',
            70 => 'Preschool Vice Principal Academic Excellence', 71 => 'Preschool Vice Principal Operations & Student Well-Being',
            72 => 'DC Coordinator', 73 => 'Secondary Vice Principal of Operations & Student Well-Being',
            74 => 'Secondary Vice Principal of Academic Excellence', 75 => 'Special Education Teacher'
        ];

        foreach ($positions as $id => $name) {
            Position::firstOrCreate(['id' => $id], ['name' => $name]);
        }

        Branch::firstOrCreate(['name' => 'Bintaro'], ['code' => 'BIN', 'is_active' => true]);
        Branch::firstOrCreate(['name' => 'Bangka'], ['code' => 'BGK', 'is_active' => true]);
        Branch::firstOrCreate(['name' => 'Semarang'], ['code' => 'SMG', 'is_active' => true]);
    }
}

