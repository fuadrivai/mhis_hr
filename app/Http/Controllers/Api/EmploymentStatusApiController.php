<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmploymentStatus;
use Illuminate\Http\Request;

class EmploymentStatusApiController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmploymentStatus  $employmentStatus
     * @return \Illuminate\Http\Response
     */
    public function show(EmploymentStatus $employmentStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmploymentStatus  $employmentStatus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmploymentStatus $employmentStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmploymentStatus  $employmentStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmploymentStatus $employmentStatus)
    {
        //
    }
}
