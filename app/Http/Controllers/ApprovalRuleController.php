<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRule;
use App\Services\BranchService;
use App\Services\EmployeeService;
use App\Services\JobLevelService;
use App\Services\OrganizationService;
use App\Services\PositionService;
use Illuminate\Http\Request;

class ApprovalRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private PositionService $positionService;
    private EmployeeService $employeeService;
    private BranchService $branchService;
    private OrganizationService $organizationService;
    private JobLevelService $jobLevelService;
    
    public function __construct(PositionService $positionService, EmployeeService $employeeService, BranchService $branchService, OrganizationService $organizationService, JobLevelService $jobLevelService)
    {
        $this->positionService = $positionService;
        $this->employeeService = $employeeService;
        $this->branchService = $branchService;
        $this->organizationService = $organizationService;
        $this->jobLevelService = $jobLevelService;
    }
    public function index()
    {
        return view('approval.rule.index',['title' => 'Approval Line']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $positions = $this->positionService->get();
        $employees = $this->employeeService->get();
        $branches = $this->branchService->get();
        $organizations = $this->organizationService->get();
        $jobLevels = $this->jobLevelService->get();
        return view('approval.rule.form',
                ['title' => 'Create Approval Rule',
                'positions' => $positions,
                'employees' => $employees,
                'branches' => $branches,
                'organizations' => $organizations,
                'jobLevels' => $jobLevels
                ]
            );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApprovalRule  $approvalRule
     * @return \Illuminate\Http\Response
     */
    public function show(ApprovalRule $approvalRule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ApprovalRule  $approvalRule
     * @return \Illuminate\Http\Response
     */
    public function edit(ApprovalRule $approvalRule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ApprovalRule  $approvalRule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ApprovalRule $approvalRule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApprovalRule  $approvalRule
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApprovalRule $approvalRule)
    {
        //
    }
}
