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
use Maatwebsite\Excel\Concerns\ToModel;

class EmployeeImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $religion = Religion::where('name', $row[31])->first();
        if (!isset($religion)) {
            $religion = new Religion();
            $religion->name = $row[31];
            $religion->save();
        }
        $personal = Personal::where('email', $row[11])->first();
        if (!isset($personal)) {
            $personal = new Personal();
            $personal->fullname = $row[1];
            $personal->barcode = $row[2];
            $personal->email = $row[11];
            $personal->address = $row[15];
            $personal->current_address = $row[16];
            $personal->birth_place = $row[14];
            $personal->birth_date = $row[12];
            $personal->phone = $row[28];
            $personal->mobile_phone = $row[27];
            $personal->gendre = $row[32] == "Male" ? "1" : "2";
            $personal->blood_type = $row[34] == "" ? null : $row[34];
            $personal->religion_id = $religion->id;
            $personal->identity_number = $row[26];
            switch ($row[32]) {
                case 'Single':
                    $personal->marital_status = 1;
                    break;
                case 'Merried':
                    $personal->marital_status = 2;
                    break;
                case 'Widow':
                    $personal->marital_status = 3;
                    break;
                case 'Widower':
                    $personal->marital_status = 3;
                    break;
                default:
                    $personal->marital_status = 1;
                    break;
            }
            $personal->save();
        }
        $organization = Organization::where('name', $row[3])->first();
        if (!isset($organization)) {
            $organization = new Organization();
            $organization->name = $row[3];
            $organization->save();
        }
        $position = Position::where('name', $row[4])->first();
        if (!isset($position)) {
            $position = new Position();
            $position->name = $row[4];
            $position->save();
        }
        $level = JobLevel::where('name', $row[5])->first();
        if (!isset($level)) {
            $level = new JobLevel();
            $level->name = $row[5];
            $level->save();
        }
        $branch = Branch::where('name', $row[29])->first();
        if (!isset($branch)) {
            $branch = new Branch();
            $branch->name = $row[29];
            if (str_contains($row[29], 'Bangka')) {
                $branch->code = "BNK";
            } elseif (str_contains($row[29], 'Bintaro')) {
                $branch->code = "BTR";
            } elseif (str_contains($row[29], 'Semarang')) {
                $branch->code = "SMR";
            } else {
                $branch->code = "HO";
            }
            $branch->save();
        }
        $employment = Employment::where('employee_id', $row[0])->first();
        if (!isset($employment)) {
            $employment = new Employment();
            $employment->employee_id = $row[0];
            $employment->barcode = $row[2];
            $employment->organization_id = $organization->id;
            $employment->organization_name = $organization->name;
            $employment->job_position_id = $position->id;
            $employment->job_position_name = $position->name;
            $employment->job_level_id = $level->id;
            $employment->job_level_name = $level->name;
            $employment->branch_id = $branch->id;
            $employment->branch_name = $branch->name;
            $employment->employment_status = strtolower($row[8]);
            $employment->join_date = $row[6];
            $employment->end_date = $row[9];
            $employment->resign_date = $row[7];
            $employment->sign_date = $row[10];
            $employment->nationality_code = $row[34];
            $employment->save();
        }
        $user = User::where('email', $row[11])->first();
        if (!isset($user)) {
            $user = new User();
            $user->name = $row[1];
            $user->email = $row[11];
            $user->password = '$2y$10$zMzXBaCLSTLnNJnPIYsN6OJHisOlgA/g6LW2kWsYN11Zq4aF2FjDS';
            $user->save();
        }
        return new Employee([
            "personal_id" => $personal->id,
            "employment_id" => $employment->id,
            "user_id" => $user->id,
            "id_talenta" => null,
        ]);
    }
}
