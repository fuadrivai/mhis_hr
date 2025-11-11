<?php

namespace App\Http\Controllers;

use App\Models\Employment;
use App\Http\Requests\StoreEmploymentRequest;
use App\Services\EmploymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class EmploymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    private EmploymentService $employmentService;
    public function __construct(EmploymentService $employmentService)
    {
        $this->employmentService = $employmentService;
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreEmploymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmploymentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employment  $employment
     * @return \Illuminate\Http\Response
     */
    public function show(Employment $employment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Employment  $employment
     * @return \Illuminate\Http\Response
     */
    public function edit(Employment $employment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEmploymentRequest  $request
     * @param  \App\Models\Employment  $employment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required',
            'organization' => 'required',
            'organization_name' => 'nullable',
            'position' => 'required',
            'job_position_name' => 'nullable',
            'approval_line' => 'nullable',
            'approval_line_name' => 'nullable',
            'level' => 'required',
            'job_level_name' => 'nullable',
            'branch' => 'required',
            'branch_name' => 'nullable',
            'employment-status' => 'required',
            'join_date' => 'required',
            'end_date' => 'nullable',
        ]);
        $this->employmentService->put($validated);
        return Redirect::to('employee');
        // return response()->json($employment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employment  $employment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employment $employment)
    {
        //
    }
}
