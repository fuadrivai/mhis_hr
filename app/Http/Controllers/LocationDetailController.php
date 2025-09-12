<?php

namespace App\Http\Controllers;

use App\Models\LocationDetail;
use App\Http\Requests\StoreLocationDetailRequest;
use App\Http\Requests\UpdateLocationDetailRequest;

class LocationDetailController extends Controller
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
     * @param  \App\Http\Requests\StoreLocationDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLocationDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LocationDetail  $locationDetail
     * @return \Illuminate\Http\Response
     */
    public function show(LocationDetail $locationDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LocationDetail  $locationDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(LocationDetail $locationDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLocationDetailRequest  $request
     * @param  \App\Models\LocationDetail  $locationDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLocationDetailRequest $request, LocationDetail $locationDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LocationDetail  $locationDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(LocationDetail $locationDetail)
    {
        //
    }
}
