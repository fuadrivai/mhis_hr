<?php

namespace App\Http\Controllers;

use App\Models\InformalEducation;
use App\Http\Requests\StoreInformalEducationRequest;
use App\Http\Requests\UpdateInformalEducationRequest;

class InformalEducationController extends Controller
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
     * @param  \App\Http\Requests\StoreInformalEducationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInformalEducationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InformalEducation  $informalEducation
     * @return \Illuminate\Http\Response
     */
    public function show(InformalEducation $informalEducation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InformalEducation  $informalEducation
     * @return \Illuminate\Http\Response
     */
    public function edit(InformalEducation $informalEducation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInformalEducationRequest  $request
     * @param  \App\Models\InformalEducation  $informalEducation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInformalEducationRequest $request, InformalEducation $informalEducation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InformalEducation  $informalEducation
     * @return \Illuminate\Http\Response
     */
    public function destroy(InformalEducation $informalEducation)
    {
        //
    }
}
