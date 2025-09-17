<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceLogService;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private AttendanceService $attendanceService;
    private AttendanceLogService $attendanceLogService;

    public function __construct(AttendanceService $attendanceService, AttendanceLogService $attendanceLogService)
    {
        $this->attendanceService = $attendanceService;
        $this->attendanceLogService = $attendanceLogService;
    }

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
        return $this->attendanceService->post($request);
    }
    public function getUserScheduleById($user_email)
    {
        return $this->attendanceService->getUserScheduleById($user_email);
    }
    public function getHistory(Request $request)
    {
        return $this->attendanceService->getHistory($request);
    }
    public function liveAttendanceList(Request $request)
    {
        return $this->attendanceService->liveAttendanceList($request);
    }
    public function getSummaryReport(Request $request)
    {
        return $this->attendanceService->getSummaryReport($request);
    }
    public function mekariOauth2()
    {
        return $this->attendanceService->mekariOauth2();
    }
    public function clockIn(Request $request)
    {
        $data =  $this->attendanceLogService->clock_in($request);
        return response()->json($data, 200);
    }
    public function clockOut(Request $request)
    {
        $data =  $this->attendanceLogService->clock_out($request);
        return response()->json($data, 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
