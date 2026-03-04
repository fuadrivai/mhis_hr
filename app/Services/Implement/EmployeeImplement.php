<?php

namespace App\Services\Implement;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeDocumentApproval;
use App\Models\EmployeeDocumentVersion;
use App\Models\EmployeeSchedule;
use App\Models\Employment;
use App\Models\PayrolInfo;
use App\Models\Personal;
use App\Models\Schedule;
use App\Models\User;
use App\Services\EmployeeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeImplement implements EmployeeService
{
    function get($request)
    {
        $employees = Employee::with(['personal', 'employment', 'activeSchedule'])->get();
        return $employees;
    }
    function paginate($request) {}
    function show($id,$with=[]) {
        return Employee::with($with)->find($id);
    }

    function post($request, $driveService)
    {
        DB::beginTransaction();
        try {
            $_personal     = json_decode($request->personal, true);
            $_employment   = json_decode($request->employment, true);
            $_payrollInfo  = json_decode($request->payrollInfo, true);

            $personal = new Personal();
            $personal->religion_id = $_personal['religionId'];
            $personal->first_name = $_personal['firstName'];
            $personal->last_name = $_personal['lastName'];
            $personal->fullname = $_personal['fullName'];
            $personal->barcode = $_personal['barcode'];
            $personal->email = $_personal['email'];
            $personal->address = $_personal['address'];
            $personal->current_address = $_personal['currentAddress'];
            $personal->birth_place = $_personal['birthPlace'];
            $personal->birth_date = $_personal['birthDate'];
            $personal->phone = $_personal['phone'];
            $personal->mobile_phone = $_personal['mobilePhone'];
            $personal->gendre = $_personal['gendre'];
            $personal->blood_type = $_personal['bloodType'];
            $personal->identity_number = $_personal['identityNumber'];
            $personal->marital_status = $_personal['maritalStatus'];
            $personal->identity_type = $_personal['identityType'];
            $personal->expired_date_identity_id = $_personal['expiredDateIdentityId'];
            $personal->postal_code = $_personal['postalCode'];
            $personal->passport_number = $_personal['passportNumber']; // FIX typo
            $personal->save();

            $employment = new Employment();
            $employment->employee_id = $_employment['employeeId'];
            $employment->organization_id = $_employment['organizationId'];
            $employment->organization_name = $_employment['organizationName'];
            $employment->job_position_id = $_employment['jobPositionId'];
            $employment->job_position_name = $_employment['jobPositionName'];
            $employment->approval_line = $_employment['approvalLine'];
            $employment->job_level_id = $_employment['jobLevelId'];
            $employment->job_level_name = $_employment['jobLevelName'];
            $employment->branch_id = $_employment['branchId'];
            $employment->branch_name = $_employment['branchName'];
            $employment->employment_status = $_employment['employmentStatus'];
            $employment->join_date = $_employment['joinDate'];
            $employment->end_date = $_employment['endDate'];
            $employment->sign_date = $_employment['signDate'];
            $employment->save();

            $payroll = new PayrolInfo();
            $payroll->bank_id = $_payrollInfo['bankId']==""?null:$_payrollInfo['bankId'];
            $payroll->account_number = $_payrollInfo['accountNumber']==""?null:$_payrollInfo['accountNumber'];
            $payroll->account_holder = $_payrollInfo['accountHolder']==""?null:$_payrollInfo['accountHolder'];
            $payroll->npwp = $_payrollInfo['npwp']==""?null:$_payrollInfo['npwp'];
            $payroll->PTKP_status = $_payrollInfo['ptkpStatus']==""?null:$_payrollInfo['ptkpStatus'];
            $payroll->bpjs_ketenagakerjaan = $_payrollInfo['bpjsKetenagakerjaan']==""?null:$_payrollInfo['bpjsKetenagakerjaan'];
            $payroll->bpjs_kesehatan = $_payrollInfo['bpjsKesehatan']==""?null:$_payrollInfo['bpjsKesehatan'];
            $payroll->employment_tax_status = $_payrollInfo['employmentTaxStatus']==""?null:$_payrollInfo['employmentTaxStatus'];
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
            $employee->schedule_id = $request->schedule;
            $employee->approval_line = $request->approvalLine;
            $employee->save();

            $schedule = Schedule::where('id', $request->schedule)->first();
            EmployeeSchedule::create([
                'employee_id' => $employee->id,
                'schedule_id' => $request->schedule,
                'schedule_name' => $schedule->name ?? null,
                'effective_start_date' => Carbon::now(),
            ]);

            $folderId = $driveService->createFolder(
                    $employment->employee_id . '-' . $personal->fullname,
                    config('google.folder_id')
            );

            if ($request->has('documents')) {
                foreach ($request->documents as $index => $doc) {
                    if (!isset($doc['file'])) {
                        continue;
                    }

                    $file = $doc['file'];
                    $upload = $driveService->uploadFile($file, $folderId);
                    $document =  EmployeeDocument::create([
                        'employee_id'               => $employee->id,
                        'document_category_id'      => $doc['category_id'],
                        'category_name'   => $doc['category_name'],
                        'document_number' => $doc['document_number'],
                        'issued_date'     => $doc['issued_date'],
                        'expiry_date'     => $doc['expiry_date'],
                        'status'          => 'approved',
                        'notes'           => $doc['notes'],
                    ]);

                    EmployeeDocumentVersion::create([
                        'employee_document_id' => $document->id,
                        'drive_file_id' => $upload['id'],
                        'file_name' => $file->getClientOriginalName(),
                        'file_url' => $upload['link'],
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'version' => 1,
                        'uploaded_by' => auth()->id(),
                        'is_latest' => true,
                    ]);

                    EmployeeDocumentApproval::create([
                        'employee_document_id' => $document->id,
                        'approver_id' => auth()->id(),
                        'status' => 'approved',
                        'notes' => 'Document uploaded and auto-approved.',
                    ]);
                }
            }
            DB::commit();
            return $employee;
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
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
