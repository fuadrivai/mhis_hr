<?php

namespace App\Services\Implement;

use App\Models\Employee;
use App\Services\AttendanceService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use function App\Helpers\getShiftByDate;
use function App\Helpers\resolveAttendanceDate;
use function App\Helpers\talentaHeader;

class AttendanceImplement implements AttendanceService
{
    function get($request) {}
    function show($id) {}
    function getHistory($request)
    {
        $client =  new Client([
            'base_uri' => $_ENV['MEKARI_API_BASE_URL']
        ]);
        $method     = 'GET';
        $path       = "/v2/talenta/v2/attendance/" . $request['user_id'] . "/history-list";
        $queryParam = "?year=" . $request['year'] . "&month=" . $request['month'];
        $headers    = ['X-Idempotency-Key' => '1234'];
        try {
            $response = $client->request(
                $method,
                $path . $queryParam,
                ['headers'   => array_merge(talentaHeader($method, $path . $queryParam), $headers)]
            );
            $res = json_decode($response->getBody());
            if (isset($res->data->attendance_history)) {
                $dataRest = $res->data->attendance_history;
                for ($i = 0; $i < count($dataRest); $i++) {
                    $att = $dataRest[$i];
                    $strLow = strtolower($att->shift);
                    $dayOff = false;
                    if (str_contains($strLow, "dayoff") || str_contains($strLow, "holiday")) {
                        $dayOff = true;
                    }
                    $att->dayoff = $dayOff;
                }
            }
            // return array_merge(talentaHeader($method, $path . $queryParam), $headers);

            $uniqueObjects = [];
            foreach ($res->data->attendance_history as $object) {
                $isDuplicate = false;
                foreach ($uniqueObjects as $uniqueObject) {
                    if ($object->date == $uniqueObject->date) { // compare by property
                        $isDuplicate = true;
                        break;
                    }
                }
                if (!$isDuplicate) {
                    $uniqueObjects[] = $object;
                }
            }

            // return $res->data->attendance_history;
            return $uniqueObjects;
        } catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
            return response()->json(PHP_EOL);
        }
    }
    function getSummaryReport($request)
    {
        $date = Carbon::now();
        $now = $date->format('Y-m-d');
        $client =  new Client([
            'base_uri' => $_ENV['MEKARI_API_BASE_URL']
        ]);
        if ($request['date']) {
            $now = $request['date'];
        }
        $method     = 'GET';
        $path       = "/v2/talenta/v3/attendance/summary-report";
        $queryParam = "?date=" . $now . "&sort=clock_in&limit=200&order=asc&clock_in=lci";
        $headers    = ['X-Idempotency-Key' => '1234'];
        try {
            $response = $client->request(
                $method,
                $path . $queryParam,
                ['headers'   => array_merge(talentaHeader($method, $path . $queryParam), $headers)]
            );
            $res = json_decode($response->getBody());
            $collection = collect($res->data->summary_attendance_report);
            $filtered = $collection->filter(function ($item) {
                if ($item->late_in > 0) {
                    if ($item->employee_id != "3024" && $item->employee_id != "2029" && $item->employee_id != "1005" && $item->employee_id != "3012") {
                        return $item;
                    }
                }
            });
            $fixData = $filtered->values()->all();
            usort($fixData, function ($a, $b) {
                return $a->full_name <=> $b->full_name; // Ascending order
            });
            return $fixData;
        } catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
            return response()->json(PHP_EOL);
        }
    }
    function getUserScheduleById($request)
    {
        $client =  new Client([
            'base_uri' => $_ENV['MEKARI_API_BASE_URL']
        ]);
        $method     = 'GET';
        $path       = "/v2/talenta/v2/live-attendance/schedule";
        $queryParam = "?user_id=" . $request;
        $headers    = ['X-Idempotency-Key' => '1234'];
        try {
            $response = $client->request(
                $method,
                $path . $queryParam,
                ['headers'   => array_merge(talentaHeader($method, $path . $queryParam), $headers)]
            );
            $res = json_decode($response->getBody());
            return $res->data->schedule;
        } catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
            return response()->json(PHP_EOL);
        }
    }
    function post($request)
    {

        $data = $request->except('file');
        $query = http_build_query($data);

        $client =  new Client([
            'base_uri' => $_ENV['MEKARI_API_BASE_URL']
        ]);

        $method     = 'POST';
        $path       = "/v2/talenta/v2/live-attendance";
        $queryParam = "?" . $query;
        $headers    = [
            'X-Idempotency-Key' => '1234',
            "Content-Type" => "multipart/form-data"
        ];

        try {
            $response = $client->request(
                $method,
                $path . $queryParam,
                [
                    'headers'   => array_merge(talentaHeader($method, $path . $queryParam), $headers),
                    'multipart' => [
                        [
                            "name" => "latitude",
                            "contents" => $request['latitude']
                        ],
                        [
                            "name" => "longitude",
                            "contents" => $request['longitude']
                        ],
                        [
                            "name" => "status",
                            "contents" => $request['status']
                        ],
                        [
                            "name" => "description",
                            "contents" => $request['description']
                        ],
                        [
                            "name" => "user_id",
                            "contents" => $request['user_id']
                        ],
                        // [
                        //     "name" => $request['file'],
                        //     "file" => fopen('/path/to/your/file.ext', 'r'),

                        // ]
                    ],
                ]
            );
            $res = json_decode($response->getBody());
            return $res;
        } catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
            return response()->json(PHP_EOL);
        }
    }
    function postAttendance($request)
    {
        try {
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    function put($request) {}
    function delete($id) {}

    function mekariOauth2()
    {
        $scope = "talenta:employee:all talenta:company:all talenta:liveattendance:all talenta:timeoff:all talenta:payroll:all talenta:reimbursement:all talenta:cost-center:all talenta:attendance:all talenta:shift:all talenta:overtime:all talenta:payroll-payment-schedule:all talenta:report:all talenta:update-payroll-component:all talenta:loan:all talenta:performance-review:all talentav3:attendance:summary-report talentav3:employee:all";
        $client =  new Client([
            'base_uri' => $_ENV['MEKARI_AUTH_BASE_URL']
        ]);

        $method     = 'POST';
        $path       = "/auth/oauth2/token";
        $queryParam = "?client_id=" . $_ENV['MEKARI_API_CLIENT_ID'] . "&response_type=code&scope=" . $scope;
        $headers    = ['X-Idempotency-Key' => '1234', "Content-Type" => "application/json"];

        $response = $client->request(
            $method,
            $path,
            [
                'headers'   => array_merge($headers),
                "data" => [
                    "client_id" => "VSkLSBDZBI7LnyPX",
                    "client_secret" => "9hntyY9mqhQLHG9G5KZVkPPk9DKqagqU",
                    "client_secret" => "9hntyY9mqhQLHG9G5KZVkPPk9DKqagqU",
                ]

            ]
        );
        // $res = json_decode($response->getBody());
        // return $res->data->schedule;
        return $response;
    }

    function liveAttendanceList($request)
    {
        $client =  new Client([
            'base_uri' => $_ENV['MEKARI_API_BASE_URL']
        ]);
        $method     = 'GET';
        $path       = "/v2/talenta/v2/live-attendance";
        $queryParam = "?user_id=" . $request['user_id'] . "&start_date=" . $request['start_date'] . "&end_date=" . $request['end_date'];
        $headers    = ['X-Idempotency-Key' => '1234'];
        try {
            $response = $client->request(
                $method,
                $path . $queryParam,
                ['headers'   => array_merge(talentaHeader($method, $path . $queryParam), $headers)]
            );
            $res = json_decode($response->getBody());
            // if (isset($res->data->attendance_history)) {
            //     $dataRest = $res->data->attendance_history;
            //     for ($i = 0; $i < count($dataRest); $i++) {
            //         $att = $dataRest[$i];
            //         $strLow = strtolower($att->shift);
            //         $dayOff = false;
            //         if (str_contains($strLow, "dayoff") || str_contains($strLow, "holiday")) {
            //             $dayOff = true;
            //         }
            //         $att->dayoff = $dayOff;
            //     }
            // }
            return $res;
            // return $res->data->attendance_history;
        } catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
            return response()->json(PHP_EOL);
        }
    }

    function getAttendanceHistory($request)
    {
        $payload = is_array($request) ? $request : $request->all();
        $userId = data_get($payload, 'user.id') ?? auth()->id();
        if (!$userId) {
            throw new \Exception('User ID is required');
        }
        $employee = Employee::where('user_id', $userId)->first();
        if (!$employee) {
            throw new \Exception('Employee not found for the given user ID');
        }
        $month = data_get($payload, 'month');
        $year = data_get($payload, 'year');
        $month = is_numeric($month) ? (int) $month : null;
        $year = is_numeric($year) ? (int) $year : null;
        
        if ($month === null || $year === null) {
            throw new \Exception('Month and year are required');
        }

        $attendances = $employee->attendances()->whereYear('date', $year)->whereMonth('date', $month)->get();


        $start = Carbon::create($year, $month, 1);
        $length = $start->daysInMonth;
        $period = collect();
        for ($i = 0; $i < $length; $i++) {
            $period->push($start->copy()->addDays($i)->toDateString());
        }

        $tempAttendance = $attendances->keyBy(fn($attendance) => $attendance->date->toDateString());

        for ($j = 0; $j < $period->count(); $j++) {
            $date = $period[$j];
            if (!isset($tempAttendance[$date])) {
                $shiftForToday = getShiftByDate($employee, $date);
                $resolved = [
                    'schedule_in' => null,
                    'schedule_out' => null,
                ];
                $holiday = 0;

                if (!($shiftForToday instanceof \Illuminate\Http\JsonResponse)) {
                    $resolved = resolveAttendanceDate($shiftForToday, $date);
                    $holiday = (int) data_get($shiftForToday, 'holiday', 0);
                }

                $attendances->push((object)[
                    'date' => Carbon::createFromFormat('Y-m-d', $date, 'UTC'),
                    'status' => 'absent',
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id,
                    'fullname' => $employee->personal->fullname ?? null,
                    'shift_name' => $employee->activeSchedule->schedule_name ?? '-',
                    'holiday' => $holiday,
                    'schedule_in' =>  $resolved['schedule_in'] ?? null,
                    'schedule_out' =>  $resolved['schedule_out'] ?? null,
                    'approvals' => []
                ]);
            }
        }

        $approvalRequests = $employee->requests()->where('status', 'pending')
                            ->whereHas('data', function ($approvalRequestDataQuery) use ($month, $year) {
                                $approvalRequestDataQuery->where(function ($dateQuery) use ($month, $year) {
                                    if ($month !== null && $year !== null) {
                                        $periodStart = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
                                        $periodEnd = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();
                                        $dateQuery->whereRaw(
                                            "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date'))) <= ?",
                                            [$periodEnd]
                                        )->whereRaw(
                                            "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.end_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date'))) >= ?",
                                            [$periodStart]
                                        );
                                    } elseif ($year !== null) {
                                        $periodStart = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
                                        $periodEnd = \Carbon\Carbon::createFromDate($year, 12, 1)->endOfYear()->toDateString();
                                        $dateQuery->whereRaw(
                                            "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date'))) <= ?",
                                            [$periodEnd]
                                        )->whereRaw(
                                            "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.end_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date'))) >= ?",
                                            [$periodStart]
                                        );
                                    } elseif ($month !== null) {
                                        $dateQuery
                                            ->orWhereRaw("MONTH(STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.start_date')), '%Y-%m-%d')) = ?", [$month])
                                            ->orWhereRaw("MONTH(STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.end_date')), '%Y-%m-%d')) = ?", [$month]);
                                    }
                                });
                            })->get();

        $approvalMap = [];
        foreach ($approvalRequests as $approvalRequest) {
            $approvalData = $approvalRequest->data;
            if ($approvalData) {
                $startDate = data_get($approvalData->payload, 'start_date') ?? data_get($approvalData->payload, 'date');
                $endDate = data_get($approvalData->payload, 'end_date') ?? data_get($approvalData->payload, 'start_date') ?? data_get($approvalData->payload, 'date');
                if ($startDate && $endDate) {
                    $currentDate = \Carbon\Carbon::parse($startDate);
                    $endDateCarbon = \Carbon\Carbon::parse($endDate);
                    while ($currentDate->lte($endDateCarbon)) {
                        $dateKey = $currentDate->toDateString();
                        if (!isset($approvalMap[$dateKey])) {
                            $approvalMap[$dateKey] = [];
                        }
                        $approvalMap[$dateKey][] = [
                            'type' => $approvalRequest->type ? $approvalRequest->type->name : null,
                            'reason' => data_get($approvalData->payload, 'reason'),
                            'status' => $approvalRequest->status,
                        ];
                        $currentDate->addDay();
                    }
                }
            }
        }

        $attendances->transform(function ($attendance) use ($approvalMap) {
            $date = $attendance->date->toDateString();
            $attendance->approvals =
                $approvalMap[$date] ?? [];
            return $attendance;
        });
        
        return $attendances->sortBy('date')->values();
    }
}
