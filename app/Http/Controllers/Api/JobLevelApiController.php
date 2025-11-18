<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobLevel;
use App\Services\JobLevelService;
use Illuminate\Http\Request;

class JobLevelApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private JobLevelService $jobLevelService;

    public function __construct(JobLevelService $jobLevelService)
    {
        $this->jobLevelService = $jobLevelService;
    }
    public function index()
    {
        $levels = $this->jobLevelService->get();
        return response()->json($levels);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $level = $this->jobLevelService->post($request);
        return response()->json($level);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JobLevel  $jobLevel
     * @return \Illuminate\Http\Response
     */
    public function show(JobLevel $jobLevel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JobLevel  $jobLevel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JobLevel $jobLevel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobLevel  $jobLevel
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobLevel $jobLevel)
    {
        //
    }
}
