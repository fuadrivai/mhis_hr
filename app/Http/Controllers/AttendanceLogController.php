<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Services\AttendanceLogService;
use Illuminate\Http\Request;

class AttendanceLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private AttendanceLogService $attendanceLogService;

    public function __construct(AttendanceLogService $attendanceLogService)
    {
        $this->attendanceLogService = $attendanceLogService;
    }
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
     * @param  \App\Http\Requests\StoreAttendanceLogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }
    public function clockin(Request $request)
    {
        $data = $this->attendanceLogService->clock_in($request);
        return response()->json($data, 200);
    }
    public function clockout(Request $request)
    {
        $this->attendanceLogService->clock_out($request);
        return response()->json(['message' => 'Clock-out recorded successfully.'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AttendanceLog  $attendanceLog
     * @return \Illuminate\Http\Response
     */
    public function show(AttendanceLog $attendanceLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AttendanceLog  $attendanceLog
     * @return \Illuminate\Http\Response
     */
    public function edit(AttendanceLog $attendanceLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAttendanceLogRequest  $request
     * @param  \App\Models\AttendanceLog  $attendanceLog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AttendanceLog $attendanceLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AttendanceLog  $attendanceLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttendanceLog $attendanceLog)
    {
        //
    }
}
