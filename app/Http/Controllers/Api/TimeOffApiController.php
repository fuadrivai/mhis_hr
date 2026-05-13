<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TimeOffService;
use Illuminate\Http\Request;

class TimeOffApiController extends Controller
{

    private TimeOffService $timeOffService;
    public function __construct(TimeOffService $timeOffService)
    {
        $this->timeOffService = $timeOffService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->timeOffService->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->timeOffService->post($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TimeOff  $timeOff
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->timeOffService->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TimeOff  $timeOff
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        return $this->timeOffService->put($id, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TimeOff  $timeOff
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->timeOffService->delete($id);
    }
}
