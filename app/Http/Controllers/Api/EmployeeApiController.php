<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\EmployeeScheduleService;
use App\Services\EmployeeService;
use App\Services\PersonalService;
use Illuminate\Http\Request;

class EmployeeApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private EmployeeService $employeeService;
    private EmployeeScheduleService $employeeScheduleService;  
    private PersonalService  $personalService;  

    public function __construct(EmployeeService $employeeService, EmployeeScheduleService $employeeScheduleService, PersonalService $personalService)
    {  
        $this->employeeService = $employeeService;
        $this->employeeScheduleService = $employeeScheduleService;
        $this->personalService = $personalService;
    }

    public function index()
    {
        // return $this->employeeService->get();
    }
    public function getByJobLevel(Request $request)
    {
        $employee =  $this->employeeService->getByJobLevel($request);
        return response()->json($employee);
    }
    public function getByuserId($id)
    {
        $employee =  $this->employeeService->getByuserId($id);
        return response()->json($employee);
    }
    public function getActiveSchedule(Request $request)
    {
        $request['user']= $request['user'];
        $employeeSchedule =  $this->employeeScheduleService->getActiveSchedule($request);
        return response()->json($employeeSchedule);
    }

    public function profile()
    {
        $request= request();
        $user = $request['user'];
        $employee =  $this->employeeService->getProfile($user);
        return response()->json($employee);
    }

    public function registerFace(Request $request)
    {
        $employee = $this->personalService->registerFace($request);
        return response()->json(['message' => 'Face registered successfully']);
    }

    public function paginate(Request $request)
    {
        $employee =  $this->employeeService->paginate($request);
        return response()->json($employee);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $this->employeeService->post($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response    
     */
    public function show($id)
    {
        return $this->employeeService->show($id)->load('personal','employment');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        //
    }
}
