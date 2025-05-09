<?php

namespace App\Http\Controllers;

use App\Models\PayrolInfo;
use App\Http\Requests\StorePayrolInfoRequest;
use App\Http\Requests\UpdatePayrolInfoRequest;

class PayrolInfoController extends Controller
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
     * @param  \App\Http\Requests\StorePayrolInfoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePayrolInfoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PayrolInfo  $payrolInfo
     * @return \Illuminate\Http\Response
     */
    public function show(PayrolInfo $payrolInfo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PayrolInfo  $payrolInfo
     * @return \Illuminate\Http\Response
     */
    public function edit(PayrolInfo $payrolInfo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePayrolInfoRequest  $request
     * @param  \App\Models\PayrolInfo  $payrolInfo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePayrolInfoRequest $request, PayrolInfo $payrolInfo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PayrolInfo  $payrolInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(PayrolInfo $payrolInfo)
    {
        //
    }
}
