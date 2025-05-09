<?php

namespace App\Http\Controllers;

use App\Models\CompanyTaxInfo;
use App\Http\Requests\StoreCompanyTaxInfoRequest;
use App\Http\Requests\UpdateCompanyTaxInfoRequest;

class CompanyTaxInfoController extends Controller
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
     * @param  \App\Http\Requests\StoreCompanyTaxInfoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompanyTaxInfoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CompanyTaxInfo  $companyTaxInfo
     * @return \Illuminate\Http\Response
     */
    public function show(CompanyTaxInfo $companyTaxInfo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CompanyTaxInfo  $companyTaxInfo
     * @return \Illuminate\Http\Response
     */
    public function edit(CompanyTaxInfo $companyTaxInfo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCompanyTaxInfoRequest  $request
     * @param  \App\Models\CompanyTaxInfo  $companyTaxInfo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompanyTaxInfoRequest $request, CompanyTaxInfo $companyTaxInfo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompanyTaxInfo  $companyTaxInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompanyTaxInfo $companyTaxInfo)
    {
        //
    }
}
