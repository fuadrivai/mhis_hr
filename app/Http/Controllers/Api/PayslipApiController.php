<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Services\PayslipService;
use Illuminate\Http\Request;

class PayslipApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private PayslipService $payslipService;
    public function __construct(PayslipService $payslipService)
    {
        $this->payslipService = $payslipService;
    }

    public function index()
    {
        $request = request();
        return $this->payslipService->get($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function post(Request $request)
    {
        return $this->payslipService->post($request);
    }
    public function store(Request $request)
    {
        // return $this->payslipService->post($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payslip  $payslip
     * @return \Illuminate\Http\Response
     */
    public function show(Payslip $payslip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payslip  $payslip
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payslip $payslip)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payslip  $payslip
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payslip $payslip)
    {
        //
    }
}
