<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PinLocation;
use App\Services\PinLocationService;
use Illuminate\Http\Request;

class PinLocationApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private PinLocationService $pinLocationService;

    public function __construct(PinLocationService $pinLocationService)
    {
        $this->pinLocationService = $pinLocationService;
    }
    public function index()
    {
        $request = request();
        return $this->pinLocationService->get($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->pinLocationService->post($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PinLocation  $pinLocation
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->pinLocationService->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PinLocation  $pinLocation
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        return $this->pinLocationService->put($id, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PinLocation  $pinLocation
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->pinLocationService->delete($id);
    }
}
