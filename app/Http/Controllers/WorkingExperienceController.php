<?php

namespace App\Http\Controllers;

use App\Models\WorkingExperience;
use App\Http\Requests\StoreWorkingExperienceRequest;
use App\Http\Requests\UpdateWorkingExperienceRequest;

class WorkingExperienceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
     * @param  \App\Http\Requests\StoreWorkingExperienceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWorkingExperienceRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkingExperience  $workingExperience
     * @return \Illuminate\Http\Response
     */
    public function show(WorkingExperience $workingExperience)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkingExperience  $workingExperience
     * @return \Illuminate\Http\Response
     */
    public function edit(WorkingExperience $workingExperience)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWorkingExperienceRequest  $request
     * @param  \App\Models\WorkingExperience  $workingExperience
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWorkingExperienceRequest $request, WorkingExperience $workingExperience)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkingExperience  $workingExperience
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkingExperience $workingExperience)
    {
        //
    }
}
