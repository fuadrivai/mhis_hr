<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Imports\EmployeeImport;
use App\Models\DocumentCategory;
use App\Models\Personal;
use App\Services\BankService;
use App\Services\BranchService;
use App\Services\EmergencyContactService;
use App\Services\EmployeeService;
use App\Services\FamilyService;
use App\Services\GoogleDriveService;
use App\Services\JobLevelService;
use App\Services\OrganizationService;
use App\Services\PositionService;
use App\Services\RelationshipService;
use App\Services\ReligionService;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
    private DocumentCategory $documentCategory;
    private GoogleDriveService $googleDriveService;
    private RelationshipService $relationshipService;
    private FamilyService $familyService;
    private EmergencyContactService $econService;

    public function __construct(
        BranchService $branchService,
        OrganizationService $organizationService,
        PositionService $positionService,
        JobLevelService $levelService,
        ReligionService $religionService,
        BankService $bankService,
        ScheduleService $scheduleService,
        EmployeeService $employeeService,
        DocumentCategory $documentCategory,
        GoogleDriveService $googleDriveService,
        RelationshipService $relationshipService,
        FamilyService $familyService,
        EmergencyContactService $econService
    ) {
        $this->branchService = $branchService;
        $this->organizationService = $organizationService;
        $this->positionService = $positionService;
        $this->levelService = $levelService;
        $this->religionService = $religionService;
        $this->bankService = $bankService;
        $this->scheduleService = $scheduleService;
        $this->employeeService = $employeeService;
        $this->documentCategory = $documentCategory;
        $this->googleDriveService = $googleDriveService;
        $this->relationshipService = $relationshipService;
        $this->familyService = $familyService;
        $this->econService = $econService;
    }
    public function filterLocation(UtilitiesRequest $request)
    {
        $employees = Employee::whereNull('pin_location_id');

        $user = auth()->user();
        if ($user && $user->roles->contains('id', 3)) {
            if ($user->employee && $user->employee->employment) {
                $branchId = $user->employee->employment->branch_id;
                $orgId = $user->employee->employment->organization_id;
                $employees->whereHas('employment', function ($q) use ($branchId, $orgId) {
                    $q->where('branch_id', $branchId)
                      ->where('organization_id', $orgId);
                });
            } else {
                $employees->where('id', 0);
            }
        }

        if ($request->ajax()) {
            return datatables()->of($employees->with(['user', 'personal', 'employment']))
                ->make(true);
        }
        return view('employee.index', [
            "title" => "Master Employee"
        ]);
    }

    public function index(Request $request)
    {
        $query =  Employee::with(['user', 'personal', 'employment'])->orderBy(
            Personal::select('fullname')->whereColumn('personals.id', 'employees.personal_id'),
            'asc'
        );

        $query->where('is_active', 1 );

        $user = auth()->user();
        if ($user && $user->roles->contains('id', 3)) {
            if ($user->employee && $user->employee->employment) {
                $branchId = $user->employee->employment->branch_id;
                $orgId = $user->employee->employment->organization_id;
                $query->whereHas('employment', function ($q) use ($branchId, $orgId) {
                    $q->where('branch_id', $branchId)
                      ->where('organization_id', $orgId);
                });
            } else {
                // If user has role 3 but no employee data, restrict to none
                $query->where('id', 0);
            }
        }

        if ($request->search) {
            $query->whereHas('personal', function ($q) use ($request) {
                $q->where('fullname', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->organization) {
            if ($request->organization != "all") {
                $query->whereHas('employment', function ($q) use ($request) {
                    $q->where('organization_id', $request->organization);
                });
            } else {
                $query->with(['user', 'personal', 'employment']);
            }
        }

        if ($request->position) {
            if ($request->position != "all") {
                $query->whereHas('employment', function ($q) use ($request) {
                    $q->where('job_position_id', $request->position);
                });
            } else {
                $query->with(['user', 'personal', 'employment']);
            }
        }

        if ($request->level) {
            if ($request->level != "all") {
                $query->whereHas('employment', function ($q) use ($request) {
                    $q->where('job_level_id', $request->level);
                });
            } else {
                $query->with(['user', 'personal', 'employment']);
            }
        }

        if ($request->branch) {
            if ($request->branch != "all") {
                $query->whereHas('employment', function ($q) use ($request) {
                    $q->where('branch_id', $request->branch);
                });
            } else {
                $query->with(['user', 'personal', 'employment']);
            }
        }

        if ($request->status) {
            if ($request->status != "all") {
                $query->whereHas('employment', function ($q) use ($request) {
                    $q->where('employment_status', $request->status);
                });
            } else {
                $query->with(['user', 'personal', 'employment']);
            }
        }

        $employees = $query->paginate($request->perpage ?? 10)->withQueryString();
        $json = json_decode($employees->toJson());
        $page = [
            "total" => $json->total,
            "from" => $json->from,
            "to" => $json->to,
        ];

        if ($request->ajax()) {
            return view('employee._list', compact('employees', 'page'))->render();
        }

        return view('employee.index-v2', [
            "title" => "Master Employee",
            "branches" => $this->branchService->get(),
            "organizations" => $this->organizationService->get(),
            "positions" => $this->positionService->get(),
            "levels" => $this->levelService->get(),
            "employees" => $employees,
            "page" => $page,
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $religions = $this->religionService->get();
        $branches = $this->branchService->get();
        $organizations = $this->organizationService->get();
        $positions = $this->positionService->get();
        $levels = $this->levelService->get();
        $banks = $this->bankService->get();
        $schedules = $this->scheduleService->get();
        $category = $this->documentCategory->get();
        $employees = Employee::with(['user', 'personal', 'employment'])->orderBy(
            Personal::select('fullname')->whereColumn('personals.id', 'employees.personal_id'),
            'asc'
        )->get();

        return view('employee.form', [
            "title" => "Add Employee",
            "religions" => $religions,
            "branches" => $branches,
            "organizations" => $organizations,
            "positions" => $positions,
            "levels" => $levels,
            "banks" => $banks,
            "schedules" => $schedules,
            "employees" => $employees,
            "categories" => $category,
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
        $employee = $this->employeeService->post(
            $request,
            $this->googleDriveService
        );

        return response()->json($employee);
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
    public function education(Employee $employee)
    {
    }

    public function portofolio(Employee $employee)
    {
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

    public function deactivate(Request $request)
    {
        try {
            $employeeIds = $request->employee_ids;
            $response = $this->employeeService->deactivate($employeeIds);

            return response()->json($response);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
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
        $employee = $this->employeeService->show($id, ['personal','personal.families']);
        $religions = $this->religionService->get();
        $relations = $this->relationshipService->get();
        return view('employee.personal', [
            "title" => "Personal",
            "data" => $employee,
            "religions" => $religions,
            "relations"=>$relations,
        ]);
    }

    public function postFamily(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname'         => 'required|string',
            'personal_id'      => 'required|integer',
            'relation_ship_id' => 'required|integer',
            'religion_id'      => 'required|integer',
            'mobile_number'    => 'nullable|string',
            'address'          => 'nullable|string',
            'id_number'        => 'nullable|string',
            'gendre'           => 'required|in:male,female,1,2',
            'marital_status'   => 'nullable|in:1,2,3,4',
            'birth_date'       => 'required|date',
            'job'              => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $family = $this->familyService->post($validator->validated());
        return response()->json($family);
    }

    public function deleteFamily($id)
    {
        return $this->familyService->delete($id);
    }

    public function postEcon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname'         => 'required|string',
            'personal_id'      => 'required|integer',
            'relation_ship_id' => 'required|integer',
            'mobile_number'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $econ = $this->econService->post($validator->validated());
        return response()->json($econ);
    }

    public function deleteEcon($id)
    {
        return $this->econService->delete($id);
    }


    public function document($id)
    {
        $employee = $this->employeeService->show($id, ['documents.category', 'documents.versions', 'documents.approvals']);
        $categories = $this->documentCategory->get();
        return view('employee.document', [
            "title" => "Document",
            "data" => $employee,
            "categories" => $categories,
        ]);
    }

    public function documentUpload($employeeId, Request $request)
    {
        $data = $this->employeeService->documentUpload($employeeId, $request, $this->googleDriveService);
        return redirect()->back()->with('message', 'Document deleted successfully');
    }
    public function deleteDocument($id)
    {
        $this->employeeService->deleteDocument($id, $this->googleDriveService);
        return redirect()->back()->with('message', 'Document deleted successfully');
    }

    public function employment($id)
    {
        $employee = $this->employeeService->show($id, ['employment']);
        $organizations = $this->organizationService->get();
        $positions = $this->positionService->get();
        $levels = $this->levelService->get();
        $branches = $this->branchService->get();
        return view('employee.employment', [
            "title" => "Employment",
            "data" => $employee,
            "organizations" => $organizations,
            "positions" => $positions,
            "levels" => $levels,
            "branches" => $branches,
        ]);
    }
}
