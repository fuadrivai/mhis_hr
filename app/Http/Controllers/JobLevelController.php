<?php

namespace App\Http\Controllers;

use App\Models\JobLevel;
use App\Services\JobLevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class JobLevelController extends Controller
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
        $data = $this->jobLevelService->get()->getContent();
        return view('settings.joblevel', [
            "data" => json_decode($data, true),
            "title" => "Setting Job Level"
        ]);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->jobLevelService->post($request);
            return Redirect::to('level');
        } catch (\Throwable $th) {
            return back()->with('message', $th->getMessage());
        }
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobLevel  $jobLevel
     * @return \Illuminate\Http\Response
     */
    public function edit(JobLevel $jobLevel)
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
    public function update($id, Request $request)
    {
        $this->jobLevelService->put($id, $request);
        return Redirect::to('level');
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
