<?php

namespace App\Services\Implement;

use App\Models\Employee;
use App\Services\GsheetLinkService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use function App\Helpers\getCalendarFilter;

class GsheetLinkImplement implements GsheetLinkService
{
    public function getSchoolCalendar()
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $employee = Employee::with([
                'employment.branch',
                'employment.organization'
            ])->where('user_id', $user->id)->first();

            if (!$employee->employment) {
                return response()->json([
                    'message' => 'Employee employment data not found'
                ], 404);
            }

            $branchCode = $employee->employment->branch->code;
            $organization = $employee->employment->organization->name;

            // Panggil helper
            [$branch, $division] = getCalendarFilter(
                $branchCode,
                $organization
            );

            $category = $user->hasRole('admin') ||
                        $user->hasRole('Management')
                ? 'Internal, Leadership'
                : 'Internal';

            $response = Http::acceptJson()
                ->get(
                    'https://calendar.mutiaraharapan.sch.id/api/schedule',
                    [
                        'branch' => $branch,
                        'division' => $division,
                        'category' => $category,
                    ]
                );

            return response()->json(
                $response->json(),
                $response->status()
            );

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }
    function getNewsletter()
    {
        try {
            $url = "https://script.google.com";
            $client =  new Client([
                'base_uri' => $url
            ]);
            $method     = 'GET';
            $path = "/macros/s/AKfycby2pXoGdSuIRFbC1HZUGAV8NSJEwQcYIbuYEMnhdqo0bbmj9qMg3ZjWfJnQP5TdnyFp/exec";
            $queryParam = "?level=Preschool,Primary,Secondary";
            $response = $client->request(
                $method,
                $path . $queryParam,
                [
                    'headers'   => [
                        "Content-Type" => "application/json"
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), $th->getCode());
        }
    }
    function getGeneralAnnouncement() {}

    function getKPI($request)
    {

        try {
            $employee = Employee::where('user_id', $request->user->id)->with('employment')->first();

            $url = "https://script.google.com";
            $client =  new Client([
                'base_uri' => $url
            ]);
            $method     = 'GET';
            $path = "";
            $queryParam = "?email=" . strtolower($request->user->email);

            $branch_code = $employee->employment->branch->code;
            $division = $employee->employment->organization->name;
            switch ($branch_code) {
                case 'BNK':
                    switch ($division) {
                        case 'Junior High':
                            $path = "/macros/s/AKfycbwtpR8Sgaz6yIcXtbgeZBLB9CwbO-YqCpvXHZql-MfSjQRI_xt0Xn8VOac48LuJCZfyFw/exec";
                            break;
                        case 'Primary':
                            $path = "/macros/s/AKfycbyw6GTNyClHRDMSzv1LK6xrL8D1lBLoii2GL4X20iJJ1-pk6QDfBxdqfsTgvjDdDh0NOw/exec";
                            break;
                        case 'Kindergarten':
                            $path = "/macros/s/AKfycbxnZP7jxW6IUyckB21u8W9WbQ9oTMFGgYCq5fx9MwgfkArJEFU6AbsBpjnuhHRi6uww0w/exec";
                            break;
                        default:
                            $path = "";
                            break;
                    }
                    break;
                case 'BTR':
                    switch ($division) {
                        case 'Secondary':
                            $path = "/macros/s/AKfycbzMhIBEa57eH8H08lwOWi4JwZZlMBIPXfl10D5pmQvQLJkhG2CGdz_S7xTOptXYDcTkEw/exec";
                            break;
                        case 'Primary':
                            $path = "/macros/s/AKfycbzTgdETKrjZINtgrELbJ1A0cDEIfLaedcS2ecz9BtdauMnpcol9dHmf6cORIMP3_hPkwg/exec";
                            break;
                        case 'Preschool':
                            $path = "/macros/s/AKfycbzaWVNm0mAPlVswbvo7LYMyGweZn8Pc1mu9Ig-rWfL4rF4eG1YDVOeDwLTBK5CK4mcd/exec";
                            break;
                        case 'Development Class':
                            $path = "/macros/s/AKfycbx4DjfwSSF6vyCQX4sEij5wY_Q3J4no_iHUBHrjxZngslV4iyRh40BoQ4l3goarE2AI/exec";
                            break;
                        default:
                            $path = "";
                            break;
                    }
                    break;
                case 'HO':
                    $path = "";
                    break;
                case 'SMG':
                    $path = "";
                    break;
                default:
                    $path = "";
                    break;
            }

            if ($path != "") {
                $response = $client->request(
                    $method,
                    $path . $queryParam,
                    [
                        'headers'   => [
                            "Content-Type" => "application/json"
                        ]
                    ]
                );
                return json_decode($response->getBody());
            } else {
                return response()->json(["message" => "No data found, please ask your principal !"], 404);
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
