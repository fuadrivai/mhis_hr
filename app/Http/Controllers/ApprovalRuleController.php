<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRule;
use App\Services\ApprovalRuleService;
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
    private ApprovalRuleService $approvalRuleService;
    
    public function __construct(PositionService $positionService, EmployeeService $employeeService, BranchService $branchService, OrganizationService $organizationService, JobLevelService $jobLevelService, ApprovalRuleService $approvalRuleService)
    {
        $this->positionService = $positionService;
        $this->employeeService = $employeeService;
        $this->branchService = $branchService;
        $this->organizationService = $organizationService;
        $this->jobLevelService = $jobLevelService;
        $this->approvalRuleService = $approvalRuleService;
    }
    public function index()
    {
        $approvals = $this->approvalRuleService->get(['steps']);
        return view('approval.rule.index',['title' => 'Approval Line', 'approvals' => $approvals]);
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
        return $this->approvalRuleService->post($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApprovalRule  $approvalRule
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $rule = $this->approvalRuleService->show($id);
        $rule->load('branch',
                'organization',
                'position',
                'level',
            );
        $rule->formatted_steps = $rule->formatted_steps;
        return response()->json($rule);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ApprovalRule  $approvalRule
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
                'jobLevels' => $jobLevels,
                'id' => $id
                ]
            );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ApprovalRule  $approvalRule
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        return $this->approvalRuleService->put($request);
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
