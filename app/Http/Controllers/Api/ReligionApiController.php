<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Religion;
use App\Services\ReligionService;
use Illuminate\Http\Request;

class ReligionApiController extends Controller
{

    private ReligionService $religionService;
    public function __construct(ReligionService $religionService)
    {
        $this->religionService = $religionService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $request = request();
        return $this->religionService->get($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->religionService->post($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Religion  $religion
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->religionService->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Religion  $religion
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        return $this->religionService->put($id, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Religion  $religion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Religion $religion)
    {
        //
    }
}
