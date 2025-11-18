<?php

namespace App\Services\Implement;

use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Employment;
use App\Models\PayrolInfo;
use App\Models\Personal;
use App\Models\User;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\DB;

class EmployeeImplement implements EmployeeService
{
    function get($request)
    {
        $employees = Employee::with(['personal', 'employment', 'activeSchedule'])->get();
        return $employees;
    }
    function paginate($request) {}
    function show($id) {}

    function post($request)
    {
        return DB::transaction(function () use ($request) {
            $personal = new Personal();
            $personal->religion_id = $request['personal']['religionId'];
            $personal->first_name = $request['personal']['firstName'];
            $personal->last_name = $request['personal']['lastName'];
            $personal->fullname = $request['personal']['fullName'];
            $personal->barcode = $request['personal']['barcode'];
            $personal->email = $request['personal']['email'];
            $personal->address = $request['personal']['address'];
            $personal->current_address = $request['personal']['currentAddress'];
            $personal->birth_place = $request['personal']['birthPlace'];
            $personal->birth_date = $request['personal']['birthDate'];
            $personal->phone = $request['personal']['phone'];
            $personal->mobile_phone = $request['personal']['mobilePhone'];
            $personal->gendre = $request['personal']['gendre'];
            $personal->blood_type = $request['personal']['bloodType'];
            $personal->identity_number = $request['personal']['identityNumber'];
            $personal->marital_status = $request['personal']['maritalStatus'];
            $personal->identity_type = $request['personal']['identityType'];
            $personal->expired_date_identity_id = $request['personal']['expiredDateIdentityId'];
            $personal->postal_code = $request['personal']['postalCode'];
            $personal->passport_number = $request['personal']['passportNumber']; // FIX typo
            $personal->save();

            $employment = new Employment();
            $employment->employee_id = $request['employment']['employeeId'];
            $employment->organization_id = $request['employment']['organizationId'];
            $employment->organization_name = $request['employment']['organizationName'];
            $employment->job_position_id = $request['employment']['jobPositionId'];
            $employment->job_position_name = $request['employment']['jobPositionName'];
            $employment->approval_line = $request['employment']['approvalLine'];
            $employment->job_level_id = $request['employment']['jobLevelId'];
            $employment->job_level_name = $request['employment']['jobLevelName'];
            $employment->branch_id = $request['employment']['branchId'];
            $employment->branch_name = $request['employment']['branchName'];
            $employment->employment_status = $request['employment']['employmentStatus'];
            $employment->join_date = $request['employment']['joinDate'];
            $employment->end_date = $request['employment']['endDate'];
            $employment->sign_date = $request['employment']['signDate'];
            $employment->save();

            $payroll = new PayrolInfo();
            $payroll->bank_id = $request['payrollInfo']['bankId'];
            $payroll->account_number = $request['payrollInfo']['accountNumber'];
            $payroll->account_holder = $request['payrollInfo']['accountHolder'];
            $payroll->npwp = $request['payrollInfo']['npwp'];
            $payroll->PTKP_status = $request['payrollInfo']['ptkpStatus'];
            $payroll->bpjs_ketenagakerjaan = $request['payrollInfo']['bpjsKetenagakerjaan'];
            $payroll->bpjs_kesehatan = $request['payrollInfo']['bpjsKesehatan'];
            $payroll->employment_tax_status = $request['payrollInfo']['employmentTaxStatus'];
            $payroll->save();

            $employee = new Employee();

            $invite = filter_var($request['inviteAccount'], FILTER_VALIDATE_BOOLEAN);

            if ($invite) {
                $user = new User();
                $user->name = $personal->fullname;
                $user->email = $personal->email;
                $user->password = bcrypt('mutiaraharapan'); 
                $user->save();

                $employee->user_id = $user->id;
            }

            $employee->personal_id = $personal->id;
            $employee->employment_id = $employment->id;
            $employee->payrol_info_id = $payroll->id;
            $employee->schedule_id = $request['schedule'];
            $employee->approval_line = $request['approvalLine'];
            $employee->save();

            return $employee;
        });
    }
    function put($request) {}
    function delete($id) {}

    function getByJobLevel($request)
    {
        $jobLevelName = $request->input('name');
        $employees =  Employee::with(['personal', 'employment.job_level','user','activeSchedule'])
            ->whereHas('employment.job_level', function ($query) use ($jobLevelName) {
                $query->where('name', $jobLevelName);
            })
            ->get();
        return $employees;
    }
    function getByuserId($userId)
    {
        $employee = Employee::with(['employment','personal'])
            ->where('user_id', auth()->id())->first();
        return $employee;
    }
}
