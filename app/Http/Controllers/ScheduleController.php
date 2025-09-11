<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Services\ScheduleService;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private ShiftService $shiftService;
    private ScheduleService $scheduleService;
    public function __construct(ShiftService $shiftService, ScheduleService $scheduleService)
    {
        $this->shiftService = $shiftService;
        $this->scheduleService = $scheduleService;
    }
    public function index()
    {
        $data = $this->scheduleService->get();
        return view('settings.time.schedule.index', [
            "title" => "Time Schedule",
            "data" => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = $this->shiftService->get();
        return view('settings.time.schedule.schedule', [
            "title" => "Time Schedule",
            "shifts" => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreScheduleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        try {
            $jsonData = json_decode($request['json-data'], true);
            $this->scheduleService->post($jsonData);
            return Redirect::to('setting/schedule');
        } catch (\Throwable $th) {
            return back()->with('message', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $schedule = $this->scheduleService->show($id);
        return response()->json($schedule);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $schedule = $this->scheduleService->show($id);
        $shifts = $this->shiftService->get();
        return view('settings.time.schedule.schedule', [
            "title" => "Time Schedule",
            "data" => $schedule,
            "shifts" => $shifts
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateScheduleRequest  $request
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $jsonData = json_decode($request['json-data'], true);
            $this->scheduleService->put($request['id'], $jsonData);
            return Redirect::to('setting/schedule');
        } catch (\Throwable $th) {
            return back()->with('message', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Schedule $schedule)
    {
        //
    }
}
