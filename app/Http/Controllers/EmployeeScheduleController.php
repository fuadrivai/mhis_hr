<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSchedule;
use App\Services\EmployeeScheduleService;
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
    private EmployeeScheduleService $employeeScheduleService;
    private ScheduleService $scheduleService;

    public function __construct(EmployeeService $employeeService, EmployeeScheduleService $employeeScheduleService, ScheduleService $scheduleService)
    {
        $this->employeeService = $employeeService;
        $this->employeeScheduleService = $employeeScheduleService;
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
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'schedule_id' => 'required|exists:schedules,id',
                'schedule_name' => 'required|string|max:255',
                'effective_start_date' => 'required|date',
            ]);

            $data = $this->employeeScheduleService->post($validated);

            return response()->json($data);
        } catch (\Throwable $th) {
            $status = ($th->getCode() && $th->getCode() >= 100 && $th->getCode() < 600) ? $th->getCode() : 500;
            return response()->json([
                'error' => 'Failed to assign schedule',
                'message' => $th->getMessage()
            ], $status);
        }
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
