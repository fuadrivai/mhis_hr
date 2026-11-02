<?php

namespace Database\Seeders;

use App\Models\EvaluationCompetency;
use Illuminate\Database\Seeder;

class EvaluationCompetencySeeder extends Seeder
{
    public function run(): void
    {
        $competencies = [
            [
                'name'        => 'Communication',
                'description' => 'Effective verbal and written communication, active listening, and clear presentation.',
                'sort_order'  => 1,
                'is_active'   => true,
            ],
            [
                'name'        => 'Problem Solving',
                'description' => 'Analytical reasoning, troubleshooting ability, and sound approach to resolving challenges.',
                'sort_order'  => 2,
                'is_active'   => true,
            ],
            [
                'name'        => 'Technical Knowledge',
                'description' => 'Role-specific domain competencies, pedagogical methods, or technical skills.',
                'sort_order'  => 3,
                'is_active'   => true,
            ],
            [
                'name'        => 'Teamwork',
                'description' => 'Interpersonal collaboration, empathy, and positive engagement with team members.',
                'sort_order'  => 4,
                'is_active'   => true,
            ],
            [
                'name'        => 'Culture Fit',
                'description' => 'Alignment with school values, code of ethics, work dedication, and professionalism.',
                'sort_order'  => 5,
                'is_active'   => true,
            ],
        ];

        foreach ($competencies as $data) {
            EvaluationCompetency::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}

