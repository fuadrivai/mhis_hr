<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Imports\EmployeeImport;
use App\Models\Personal;
use App\Services\BankService;
use App\Services\BranchService;
use App\Services\EmployeeService;
use App\Services\JobLevelService;
use App\Services\OrganizationService;
use App\Services\PositionService;
use App\Services\ReligionService;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Utilities\Request as UtilitiesRequest;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private BranchService $branchService;
    private OrganizationService $organizationService;
    private PositionService $positionService;
    private JobLevelService $levelService;
    private ReligionService $religionService;
    private BankService $bankService;
    private ScheduleService $scheduleService;
    private EmployeeService $employeeService;

    public function __construct(
        BranchService $branchService,
        OrganizationService $organizationService,
        PositionService $positionService,
        JobLevelService $levelService,
        ReligionService $religionService,
        BankService $bankService,
        ScheduleService $scheduleService,
        EmployeeService $employeeService
    ) {
        $this->branchService = $branchService;
        $this->organizationService = $organizationService;
        $this->positionService = $positionService;
        $this->levelService = $levelService;
        $this->religionService = $religionService;
        $this->bankService = $bankService;
        $this->scheduleService = $scheduleService;
        $this->employeeService = $employeeService;
    }
    public function filterLocation(UtilitiesRequest $request)
    {
        $employees = Employee::where('pin_location_id', null);
        if ($request->ajax()) {
            return datatables()->of($employees->with(['user', 'personal', 'employment']))
                ->make(true);
        }
        return view('employee.index', [
            "title" => "Master Employee"
        ]);
    }
    public function index()
    {
        $branches = $this->branchService->get()->getContent();
        $organizations = $this->organizationService->get()->getContent();
        $positions = $this->positionService->get()->getContent();
        $levels = $this->levelService->get()->getContent();

        // get employees
        $employees = Employee::with(['user', 'personal', 'employment'])->whereHas('employment', function ($query) {
            $query->where('status', 1);
        })->orderBy(
            Personal::select('fullname')->whereColumn('personals.id', 'employees.personal_id'),
            'asc'
        );

        if (request('search')) {
            $employees->whereHas('personal', function ($query) {
                $query->where('fullname', 'like', '%' . request("search") . '%')
                    ->orWhere('email', 'like', '%' . request("search") . '%');
            });
        }

        if (request('organization')) {
            if (request('organization') != "all") {
                $employees->whereHas('employment', function ($query) {
                    $query->where('organization_id', request("organization"));
                });
            } else {
                $employees->with(['user', 'personal', 'employment']);
            }
        }

        if (request('position')) {
            if (request('position') != "all") {
                $employees->whereHas('employment', function ($query) {
                    $query->where('job_position_id', request("position"));
                });
            } else {
                $employees->with(['user', 'personal', 'employment']);
            }
        }

        if (request('level')) {
            if (request('level') != "all") {
                $employees->whereHas('employment', function ($query) {
                    $query->where('job_level_id', request("level"));
                });
            } else {
                $employees->with(['user', 'personal', 'employment']);
            }
        }

        if (request('branch')) {
            if (request('branch') != "all") {
                $employees->whereHas('employment', function ($query) {
                    $query->where('branch_id', request("branch"));
                });
            } else {
                $employees->with(['user', 'personal', 'employment']);
            }
        }

        if (request('status')) {
            if (request('status') != "all") {
                $employees->whereHas('employment', function ($query) {
                    $query->where('employment_status', request("status"));
                });
            } else {
                $employees->with(['user', 'personal', 'employment']);
            }
        }

        $records = $employees->paginate(request('perpage') ?? 5)->withQueryString();
        $json = json_decode($records->toJson());

        return view('employee.index-v2', [
            "title" => "Master Employee",
            "branches" => json_decode($branches, true),
            "organizations" => json_decode($organizations, true),
            "positions" => json_decode($positions, true),
            "levels" => json_decode($levels, true),
            "employees" => $records,
            "search" => request('search'),
            "query" => [
                "search" => request('search'),
                "perpage" => request('perpage'),
                "organization" => request('organization'),
                "position" => request('position'),
                "level" => request('level'),
                "status" => request('status'),
                "branch" => request('branch'),
            ],
            "page" => [
                "total" => $json->total,
                "from" => $json->from,
                "to" => $json->to,
                // "per_page" => $json->per_page,
                // "current_page" => $json->current_page,
                // "last_page" => $json->last_page,
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $religions = $this->religionService->get()->getContent();
        $branches = $this->branchService->get()->getContent();
        $organizations = $this->organizationService->get()->getContent();
        $positions = $this->positionService->get()->getContent();
        $levels = $this->levelService->get()->getContent();
        $banks = $this->bankService->get()->getContent();
        $schedules = $this->scheduleService->get()->getContent();
        $employees = Employee::with(['user', 'personal', 'employment'])->orderBy(
            Personal::select('fullname')->whereColumn('personals.id', 'employees.personal_id'),
            'asc'
        )->get();

        return view('employee.form', [
            "title" => "Add Employee",
            "religions" => json_decode($religions, true),
            "branches" => json_decode($branches, true),
            "organizations" => json_decode($organizations, true),
            "positions" => json_decode($positions, true),
            "levels" => json_decode($levels, true),
            "banks" => json_decode($banks, true),
            "schedules" => json_decode($schedules, true),
            "employees" => $employees,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreEmployeeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        try {
            return $this->employeeService->post($request)->getContent();
            // return Redirect::to('employee');
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        // return view('employee.info', [
        //     "title" => "Add Employee",
        //     "data"=>$employee,
        // ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        return view('employee.info', [
            "title" => "Employee Information",
            "data" => $employee,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEmployeeRequest  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
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

    public function import_excel(Request $request)
    {
        // validasi
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');

        $fileName = rand() . $file->getClientOriginalName();

        $file->move('file_employee', $fileName);

        // import data
        Excel::import(new EmployeeImport, public_path('/file_employee/' . $fileName));

        // notifikasi dengan session
        session()->flash('message', 'Data Siswa Berhasil Diimport!');

        // alihkan halaman kembali
        return Redirect::to('employee');
    }

    public function personal($id)
    {

        $employee = Employee::with(['personal'])->find($id);
        $religions = $this->religionService->get()->getContent();
        return view('employee.personal', [
            "title" => "Personal",
            "data" => $employee,
            "religions" => json_decode($religions, true),
        ]);
    }
    public function employment($id)
    {
        $employee = Employee::with(['employment'])->find($id);
        $organizations = $this->organizationService->get()->getContent();
        $positions = $this->positionService->get()->getContent();
        $levels = $this->levelService->get()->getContent();
        return view('employee.employment', [
            "title" => "Employment",
            "data" => $employee,
            "organizations" => json_decode($organizations, true),
            "positions" => json_decode($positions, true),
            "levels" => json_decode($levels, true),
        ]);
    }
}
