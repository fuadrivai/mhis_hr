<?php

namespace Database\Seeders;

use App\Models\JobRequirement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $jobRequirements = [
            [
                'title' => 'Preschool Teacher',
                'description' => implode("\n\n", [
                    'MHIS Bintaro is expanding! We are looking for passionate and experienced Preschool Teachers to join our growing academic team for the 2027/2028 Academic Year.',
                    'Inspire early learners through engaging, play-based activities that nurture curiosity, creativity, and independence.',
                ]),
                'footer' => 'Apply now and grow with MHIS!',
                'requirements' => [
                    "Bachelor's degree in Early Childhood Education (PAUD/PGPAUD), English Education, or a relevant field.",
                    'Minimum 2 years of teaching experience at the preschool level.',
                    'Strong understanding of early childhood development and play-based learning.',
                    "Able to plan engaging, age-appropriate activities that support children's holistic development.",
                    'Good command of English, both written and spoken.',
                    'Patient, caring, creative, and enthusiastic about working with young children.',
                    'Strong classroom management and communication skills.',
                    'Able to build positive relationships with children and parents.',
                    'Willing to work at MHIS Bintaro.',
                    'Available to join for the 2027/2028 Academic Year.',
                ],
            ],
            [
                'title' => 'Primary Teacher',
                'description' => implode("\n\n", [
                    'MHIS Bintaro is expanding! We are looking for passionate and experienced Primary Teachers to join our growing academic team for the 2027/2028 Academic Year.',
                    "We are currently opening opportunities for teachers in:\n- English\n- Mathematics\n- Science\n- Homeroom / Classroom Teaching\n- Digital Literacy\n- Religion Studies\n- Physical Education\n- Music (Part-time)",
                    'Inspire primary learners through engaging, student-centered lessons while nurturing their academic skills and confidence.',
                ]),
                'footer' => 'Apply now and grow with MHIS!',
                'requirements' => [
                    "Bachelor's degree in Primary Education (PGSD/PGMI) for Homeroom / Classroom Teacher positions, or Education or a relevant subject-related field for subject teachers.",
                    'Minimum 2 years of teaching experience in a Cambridge school or a school offering a UK-based curriculum.',
                    'Relevant educational background aligned with the subject applied for.',
                    'Strong understanding of primary teaching methods and child development.',
                    'Able to design engaging, differentiated lessons.',
                    'Strong classroom management and communication skills.',
                    'Good command of English, both written and spoken, particularly for subjects taught in English.',
                    'Willing to work at MHIS Bintaro.',
                    'Available to join for the 2027/2028 Academic Year.',
                ],
            ],
            [
                'title' => 'Secondary Teacher',
                'description' => implode("\n\n", [
                    'MHIS Bintaro is expanding! We are looking for passionate and experienced Secondary Teachers for All Subjects to join our growing academic team.',
                    "We are currently opening opportunities for teachers in:\n- English\n- Mathematics\n- Science (Physics, Chemistry, and Biology)\n- Computer Science / ICT\n- Social Studies\n- Business Studies\n- Economics\n- Bahasa Indonesia\n- Pancasila Education\n- Religion Studies\n- Physical Education (Part-time)\n- Music (Part-time)",
                    'Join an international learning environment where you can inspire secondary students through engaging, student-centered lessons while nurturing strong academic skills and critical thinking.',
                ]),
                'footer' => "Ready to inspire the next generation?\n\nApply now and grow with MHIS!",
                'requirements' => [
                    "Bachelor's degree in Education or a relevant subject-related field.",
                    'Minimum 2 years of teaching experience in a Cambridge school or a school offering a UK-based curriculum.',
                    'Relevant educational background aligned with the subject applied for.',
                    'Strong subject knowledge and classroom management skills.',
                    'Able to design and deliver engaging, differentiated lessons.',
                    'Good command of English, both written and spoken, particularly for subjects taught in English.',
                    'Willing to work at MHIS Bintaro.',
                    'Available to join for the 2027/2028 Academic Year.',
                ],
            ],
        ];

        DB::transaction(function () use ($jobRequirements) {
            foreach ($jobRequirements as $data) {
                $jobRequirement = JobRequirement::updateOrCreate(
                    ['title' => $data['title']],
                    [
                        'description' => $data['description'],
                        'footer' => $data['footer'],
                        'is_active' => true,
                    ]
                );

                $jobRequirement->details()->delete();
                foreach ($data['requirements'] as $requirement) {
                    $jobRequirement->details()->create(['requirement' => $requirement]);
                }
            }
        });
    }
}
