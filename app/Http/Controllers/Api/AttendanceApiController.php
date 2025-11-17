<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        try {
            $validated = $request->validate([
                'date'      => 'required|date_format:Y-m-d H:i:s',
                'latitude'  => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'photo'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
            $validated['user'] = $request->user;
            $data =  $this->attendanceLogService->clock_in($validated);
            return response()->json($data, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        }
    }
    public function clockOut(Request $request)
    {
        $data =  $this->attendanceLogService->clock_out($request);
        return response()->json($data, 200);
    }
    
    public function liveAttendanceGa(){
        try {
            $validated = request()->validate([
                'date'          => 'required|date_format:Y-m-d H:i:s',
                'latitude'      => 'required',
                'longitude'     => 'required',
                'photo'         => 'nullable|string',
                'user_id'       => 'required',
                'type'          => 'required',
            ]);
            $validated['user'] = User::find($validated['user_id']);
            if($validated['type']=='clock_in'){
                $data =  $this->attendanceLogService->clock_in($validated);
            }else if($validated['type']=='clock_out'){
                $data =  $this->attendanceLogService->clock_out($validated);
            }else{
                return response()->json(['message' => 'Invalid type'], 422);
            }
            return response()->json($data, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        }
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
