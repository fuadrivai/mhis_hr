<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSchedule;
use App\Services\EmployeeService;
use App\Services\ScheduleService;
use Illuminate\Http\Request;

class EmployeeScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private EmployeeService $employeeService;
    private ScheduleService $scheduleService;

    public function __construct(EmployeeService $employeeService, ScheduleService $scheduleService)
    {
        $this->employeeService = $employeeService;
        $this->scheduleService = $scheduleService;
    }

    public function index()
    {
        $employee = $this->employeeService->get(request());
        $schedules = $this->scheduleService->get();
        return view('employee.scheduler.index', [
            "data" => $employee,
            "schedules" => $schedules,
            "title" => "Scheduler"
        ]);
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
     * @param  \App\Models\EmployeeSchedule  $employeeSchedule
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeSchedule $employeeSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmployeeSchedule  $employeeSchedule
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeSchedule $employeeSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmployeeSchedule  $employeeSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeSchedule $employeeSchedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmployeeSchedule  $employeeSchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeSchedule $employeeSchedule)
    {
        //
    }
}
