<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobLevel;
use App\Models\Organization;
use App\Models\Personal;
use App\Models\Position;
use App\Models\Religion;
use App\Models\User;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public static function templateHeadings(): array
    {
        return [
            'Employee ID',
            'Full Name',
            'Barcode',
            'Organization',
            'Job Position',
            'Job Level',
            'Join Date',
            'Resign Date',
            'Employment Status',
            'End Date',
            'Sign Date',
            'Email',
            'Birth Date',
            'Birth Place',
            'Address',
            'Current Address',
            'Identity Number',
            'Mobile Phone',
            'Phone',
            'Branch',
            'Religion',
            'Gender',
            'Marital Status',
            'Blood Type',
            'Nationality Code',
        ];
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'organization' => 'required|string|max:255',
            'job_position' => 'required|string|max:255',
            'job_level' => 'required|string|max:255',
            'join_date' => 'nullable|date_format:Y-m-d',
            'resign_date' => 'nullable|date_format:Y-m-d',
            'employment_status' => 'required|in:permanent,contract,probation,freelance',
            'end_date' => 'nullable|date_format:Y-m-d',
            'sign_date' => 'nullable|date_format:Y-m-d',
            'email' => 'required|email|max:255',
            'birth_date' => 'nullable|date_format:Y-m-d',
            'birth_place' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'current_address' => 'nullable|string|max:255',
            'identity_number' => 'nullable|string|max:255',
            'mobile_phone' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'branch' => 'required|string|max:255',
            'religion' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'marital_status' => 'nullable|in:Single,Married,Merried,Widow,Widower',
            'blood_type' => 'nullable|in:A,B,AB,O',
            'nationality_code' => 'nullable|string|max:255',
        ];
    }

    public function prepareForValidation($data, $index)
    {
        foreach (['join_date', 'resign_date', 'end_date', 'sign_date', 'birth_date'] as $field) {
            $value = $data[$field] ?? null;

            if ($value instanceof \DateTimeInterface) {
                $data[$field] = $value->format('Y-m-d');
            } elseif (is_numeric($value) && (float) $value > 0 && (float) $value < 2958466) {
                $data[$field] = Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }
        }

        foreach ($data as $field => $value) {
            if (is_string($value)) {
                $value = trim($value);
                $data[$field] = $value === '' ? null : $value;
            } elseif (is_scalar($value) && $value !== null) {
                $data[$field] = (string) $value;
            }
        }

        if (isset($data['employment_status'])) {
            $data['employment_status'] = strtolower($data['employment_status']);
        }

        if (isset($data['gender'])) {
            $data['gender'] = ucfirst(strtolower($data['gender']));
        }

        if (isset($data['marital_status'])) {
            $data['marital_status'] = ucfirst(strtolower($data['marital_status']));
        }

        if (isset($data['blood_type'])) {
            $data['blood_type'] = strtoupper($data['blood_type']);
        }

        return $data;
    }

    public function model(array $row)
    {
        $religion = Religion::firstOrCreate(['name' => $row['religion']]);

        $personal = Personal::where('email', $row['email'])->first();
        if (!$personal) {
            $personal = new Personal();
            $personal->fullname = $row['full_name'];
            $personal->barcode = $row['barcode'] ?? null;
            $personal->email = $row['email'];
            $personal->address = $row['address'] ?? null;
            $personal->current_address = $row['current_address'] ?? null;
            $personal->birth_place = $row['birth_place'] ?? null;
            $personal->birth_date = $row['birth_date'] ?? null;
            $personal->phone = $row['phone'] ?? null;
            $personal->mobile_phone = $row['mobile_phone'] ?? null;
            $personal->gendre = $row['gender'] === 'Male' ? '1' : '2';
            $personal->blood_type = $row['blood_type'] ?? null;
            $personal->religion_id = $religion->id;
            $personal->identity_number = $row['identity_number'] ?? null;
            $personal->marital_status = $this->maritalStatus($row['marital_status'] ?? null);
            $personal->save();
        }

        $organization = Organization::firstOrCreate(['name' => $row['organization']]);
        $position = Position::firstOrCreate(['name' => $row['job_position']]);
        $level = JobLevel::firstOrCreate(['name' => $row['job_level']]);

        $branch = Branch::where('name', $row['branch'])->first();
        if (!$branch) {
            $branch = new Branch();
            $branch->name = $row['branch'];

            if (str_contains($row['branch'], 'Bangka')) {
                $branch->code = 'BNK';
            } elseif (str_contains($row['branch'], 'Bintaro')) {
                $branch->code = 'BTR';
            } elseif (str_contains($row['branch'], 'Semarang')) {
                $branch->code = 'SMR';
            } else {
                $branch->code = 'HO';
            }

            $branch->save();
        }

        $employment = Employment::where('employee_id', $row['employee_id'])->first();
        if (!$employment) {
            $employment = new Employment();
            $employment->employee_id = $row['employee_id'];
            $employment->barcode = $row['barcode'] ?? null;
            $employment->organization_id = $organization->id;
            $employment->organization_name = $organization->name;
            $employment->job_position_id = $position->id;
            $employment->job_position_name = $position->name;
            $employment->job_level_id = $level->id;
            $employment->job_level_name = $level->name;
            $employment->branch_id = $branch->id;
            $employment->branch_name = $branch->name;
            $employment->employment_status = $row['employment_status'];
            $employment->join_date = $row['join_date'] ?? null;
            $employment->end_date = $row['end_date'] ?? null;
            $employment->resign_date = $row['resign_date'] ?? null;
            $employment->sign_date = $row['sign_date'] ?? null;
            $employment->nationality_code = $row['nationality_code'] ?? null;
            $employment->save();
        }

        $user = User::where('email', $row['email'])->first();
        if (!$user) {
            $user = new User();
            $user->name = $row['full_name'];
            $user->email = $row['email'];
            $user->password = '$2y$10$zMzXBaCLSTLnNJnPIYsN6OJHisOlgA/g6LW2kWsYN11Zq4aF2FjDS';
            $user->save();
        }

        $employee = Employee::where('personal_id', $personal->id)
            ->orWhere('employment_id', $employment->id)
            ->orWhere('user_id', $user->id)
            ->first();

        if ($employee) {
            return null;
        }

        return new Employee([
            'personal_id' => $personal->id,
            'employment_id' => $employment->id,
            'user_id' => $user->id,
            'id_talenta' => null,
        ]);
    }

    private function maritalStatus(?string $status): int
    {
        switch (strtolower($status ?? '')) {
            case 'married':
            case 'merried':
                return 2;
            case 'widow':
                return 3;
            case 'widower':
                return 4;
            case 'single':
            default:
                return 1;
        }
    }
}
