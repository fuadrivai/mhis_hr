<?php

namespace App\Http\Controllers;

class RecruitmentController extends Controller
{
    private const SECTIONS = [
        'dashboard' => 'Recruitment Dashboard',
        'candidates' => 'Candidates',
        'applications' => 'Applications',
        'pipeline' => 'Recruitment Pipeline',
        'interviews' => 'Interview Schedule',
        'assessments' => 'Assessments & Evaluations',
        'final-review' => 'Final Review',
        'talent-pool' => 'Talent Pool',
        'reports' => 'Recruitment Reports',
        'settings' => 'Recruitment Settings',
    ];

    public function index($section = 'dashboard')
    {
        $user = auth()->user();

        if (!$user->canAccessRecruitment()) {
            abort(403);
        }

        abort_unless(isset(self::SECTIONS[$section]), 404);

        if ($section === 'candidates') {
            return redirect()->route('recruitment.candidates');
        }

        $title = self::SECTIONS[$section];

        return view('recruitment.index', [
            'title' => $title,
            'section' => $section,
        ]);
    }
}
