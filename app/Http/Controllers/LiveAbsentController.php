<?php

namespace App\Http\Controllers;

use App\Models\LiveAbsent;
use App\Http\Requests\StoreLiveAbsentRequest;
use App\Http\Requests\UpdateLiveAbsentRequest;

class LiveAbsentController extends Controller
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
     * @param  \App\Http\Requests\StoreLiveAbsentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLiveAbsentRequest $request)
    {
        //
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LiveAbsent  $liveAbsent
     * @return \Illuminate\Http\Response
     */
    public function edit(LiveAbsent $liveAbsent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLiveAbsentRequest  $request
     * @param  \App\Models\LiveAbsent  $liveAbsent
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLiveAbsentRequest $request, LiveAbsent $liveAbsent)
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
