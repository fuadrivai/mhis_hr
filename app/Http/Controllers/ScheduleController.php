<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Services\ScheduleService;
use App\Services\ShiftService;

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
        return view('settings.time.schedule.index', [
            "title" => "Time Schedule"
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = $this->shiftService->get()->getContent();
        return view('settings.time.schedule.schedule', [
            "title" => "Time Schedule",
            "shifts" => json_decode($data, true)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreScheduleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreScheduleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function show(Schedule $schedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(Schedule $schedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateScheduleRequest  $request
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        //
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
