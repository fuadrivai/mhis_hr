<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LiveAbsent;
use App\Services\LiveAbsentService;
use Illuminate\Http\Request;

class LiveAbsentApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private LiveAbsentService $liveAbsentService;

    public function __construct(LiveAbsentService $liveAbsentService)
    {
        $this->liveAbsentService = $liveAbsentService;
    }
    public function index()
    {
        //
    }
    public function filterByUser()
    {
        return $this->liveAbsentService->filterByUser(request());
    }
    public function getCity($city)
    {
        return $this->liveAbsentService->getCity($city);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->liveAbsentService->post(request());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LiveAbsent  $liveAbsent
     * @return \Illuminate\Http\Response
     */
    public function show(LiveAbsent $liveAbsent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LiveAbsent  $liveAbsent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LiveAbsent $liveAbsent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LiveAbsent  $liveAbsent
     * @return \Illuminate\Http\Response
     */
    public function destroy(LiveAbsent $liveAbsent)
    {
        //
    }
}
