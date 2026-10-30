<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentMonitor;
use App\Services\RecruitmentMonitorService;
use Illuminate\Http\Request;

class RecruitmentMonitorController extends Controller
{
    public function __construct(private RecruitmentMonitorService $recruitmentMonitorService)
    {
    }

    public function index()
    {
        return view('settings.recruiment.index', array_merge(
            $this->recruitmentMonitorService->getMonitorPageData(auth()->user()),
            ['title' => 'Recruitment Monitoring']
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
        ]);
        $this->recruitmentMonitorService->addMonitor($validated['employee_id'], auth()->user());

        return redirect()
            ->route('settings.recruitment-monitors')
            ->with('success', 'Recruitment monitor added. The employee can now access Recruitment.');
    }

    public function destroy(RecruitmentMonitor $monitor)
    {
        $this->recruitmentMonitorService->removeMonitor($monitor, auth()->user());

        return redirect()
            ->route('settings.recruitment-monitors')
            ->with('success', 'Recruitment monitor access removed.');
    }
}
