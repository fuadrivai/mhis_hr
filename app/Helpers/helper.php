<?php

namespace App\Helpers;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use Carbon\Carbon;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManagerStatic as Image;

function talentaSandboxHeader($method, $pathWithQueryParam)
{
    $datetime       = Carbon::now()->toRfc7231String();
    $request_line   = "{$method} {$pathWithQueryParam} HTTP/1.1";
    $payload        = implode("\n", ["date: {$datetime}", $request_line]);
    $digest         = hash_hmac('sha256', $payload, $_ENV['SANDBOX_API_CLIENT_SECRET'], true);
    $signature      = base64_encode($digest);

    return [
        'Content-Type'  => 'application/json',
        'Date'          => $datetime,
        'Authorization' => "hmac username=\"{$_ENV['SANDBOX_API_CLIENT_ID']}\", algorithm=\"hmac-sha256\", headers=\"date request-line\", signature=\"{$signature}\""
    ];
}
function talentaHeader($method, $pathWithQueryParam)
{
    $datetime       = Carbon::now()->toRfc7231String();
    $request_line   = "{$method} {$pathWithQueryParam} HTTP/1.1";
    $payload        = implode("\n", ["date: {$datetime}", $request_line]);
    $digest         = hash_hmac('sha256', $payload, $_ENV['MEKARI_API_CLIENT_SECRET'], true);
    $signature      = base64_encode($digest);

    return [
        'Content-Type'  => 'application/json',
        'Date'          => $datetime,
        'Authorization' => "hmac username=\"{$_ENV['MEKARI_API_CLIENT_ID']}\", algorithm=\"hmac-sha256\", headers=\"date request-line\", signature=\"{$signature}\""
    ];
}

function getUserTalentaByEmail($email)
{
    $client =  new Client([
        'base_uri' => $_ENV['MEKARI_API_BASE_URL']
    ]);

    $method     = 'GET';
    $path       = '/v2/talenta/v3/employees';
    $queryParam = "?email=" . $email;
    $headers    = ['X-Idempotency-Key' => '1234'];

    try {
        $response = $client->request(
            $method,
            $path . $queryParam,
            ['headers'   => array_merge(talentaHeader($method, $path . $queryParam), $headers)]
        );

        $res = json_decode($response->getBody());
        $count = count($res->data->employees);
        return $count > 0 ? $res->data->employees[0] : null;
    } catch (ClientException $e) {
        echo Psr7\Message::toString($e->getRequest());
        echo Psr7\Message::toString($e->getResponse());
        return response()->json(PHP_EOL);
    }
}

function sendMessage(string $deviceToken, array $d): array
{
    try {

        $credential = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            json_decode(
                file_get_contents(storage_path('token/mhis-hub-87009f572c21.json')),
                true
            )
        );

        $token = $credential->fetchAuthToken(
            HttpHandlerFactory::build()
        );

        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $d['title'],
                    'body'  => $d['body'],
                ],
                'data' => [
                    'title' => $d['title'],
                    'body'  => $d['body'],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $d['title'],
                                'body'  => $d['body'],
                            ],
                            'badge' => 1,
                            'sound' => 'default',
                        ],
                    ],
                ],
            ],
        ];

        if (!empty($d['image'])) {
            $payload['message']['notification']['image'] = $d['image'];
        }

        $ch = curl_init(
            'https://fcm.googleapis.com/v1/projects/mhis-hub/messages:send'
        );

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token['access_token'],
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {

            $error = curl_error($ch);

            curl_close($ch);

            return [
                'success' => false,
                'error'   => $error,
            ];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $decoded = json_decode($response, true);

        return [
            'success'   => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response'  => $decoded,
            'raw'       => $response,
        ];

    } catch (\Throwable $e) {

        return [
            'success' => false,
            'error'   => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ];
    }
}

function distance($lat1, $lon1, $lat2, $lon2, $unit)
{

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
        return ($miles * 1.609344);
    } else if ($unit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}

function diffTime($start, $end)
{
    $startTime = Carbon::parse($start);
    $endTime = Carbon::parse($end);

    $hours = $startTime->diffInHours($endTime);
    $minutes = $startTime->diffInMinutes($endTime) % 60;

    return "$hours" . "h " . $minutes . "m";
}

function getShiftByDate(Employee $employee,$date)
{
    $shiftLength = $employee->activeSchedule->schedule->count_detail;
    $target = Carbon::parse($date)->startOfDay();
    $effective = Carbon::parse($employee->activeSchedule->effective_start_date)->startOfDay();
    $diffDays = $effective->diffInDays($target, false);

    if ($diffDays < 0) {
        return response()->json(['message' => 'Your schedule is not yet active on that date'], 400);
    }

    $dayNumber = ($diffDays % $shiftLength) + 1;
    $shiftForToday = $employee->activeSchedule->schedule->details
        ->where('number', $dayNumber)
        ->first()
        ->shift;
    return $shiftForToday;
}

function resolveAttendanceDate($shift, $clock)
{
    $clockTime = Carbon::parse($clock);
    $scheduleOut = Carbon::parse($shift->schedule_out);

    if (!$shift->is_overnight) {
        return [
            'attendance_date' => $clockTime->toDateString(),
            'schedule_in' => $shift->schedule_in,
            'schedule_out' => $shift->schedule_out,
        ];
    }

    if ($clockTime->format('H:i:s') < $scheduleOut->format('H:i:s')) {
        return [
            'attendance_date' => $clockTime->copy()->subDay()->toDateString(),
            'schedule_in' => $shift->schedule_in,
            'schedule_out' => $shift->schedule_out,
        ];
    }

    return [
        'attendance_date' => $clockTime->toDateString(),
        'schedule_in' => $shift->schedule_in,
        'schedule_out' => $shift->schedule_out,
    ];
}


function validateUser(array $data)
{
    $user = $data['user'] ?? null;
    if (!$user) {
        throw new \Exception('Unauthenticated', 403);
    }
    return $user;
}

function getEmployee($userId)
{
    $employee = Employee::with([
        'personal',
        'activeSchedule.schedule.details.shift',
        'location.details'
    ])
    ->where('user_id', $userId)
    ->first();
    if (!$employee) {
        throw new \Exception('Employee not found', 404);
    }
    return $employee;
}

function validateLocation($employee, $data)
{
    $location = $employee->location;
    if (!$location) {
        throw new \Exception(
            'Employee does not have a location assigned',
            400
        );
    }
    if (!$location->need_location) {
        return;
    }
    foreach ($location->details as $detail) {
        $distanceInKM = distance(
            $data['latitude'],
            $data['longitude'],
            $detail->latitude,
            $detail->longitude,
            "K"
        );
        $distance = $distanceInKM * 1000;
        if ($distance <= (float)$detail->radius) {
            return;
        }
        $data['radius'] = $detail->radius;
    }
    throw new \Illuminate\Http\Exceptions\HttpResponseException(
        response()->json(['message' => "Out of coverage area"], 422)
    );
}

function prepareAttendance($employee,$user,$clockTime) {
    $shift = getShiftByDate($employee, $clockTime);
    $resolved = resolveAttendanceDate($shift,$clockTime);
    $attendanceDate = $resolved['attendance_date'];

    $attendance = Attendance::firstOrCreate(
        [
            'employee_id' => $employee->id,
            'date' => $attendanceDate,
        ],
        [
            'user_id' => $user['id'],
            'fullname' => $employee->personal->fullname,
            'shift_name' => $employee->activeSchedule->schedule_name ?? '-',
            'status' => 'present',
            'holiday' => $shift->holiday ? 1 : 0,
            'schedule_in' => $resolved['schedule_in'],
            'schedule_out' => $resolved['schedule_out'],
        ]
    );
    return [
        $attendance,
        $attendanceDate
    ];
}

    function handlePhotoAndFaceRecognition($employee,?string $photo): ?string
    {
        if (!$photo) {
            return null;
        }
        // upload photo
        $photoPath = storeAttendancePhoto($employee,$photo);
        verifyFaceRecognition($employee,$photoPath);
        return $photoPath;
    }

    function storeAttendancePhoto($employee,string $photo): string
    {
        $rawPhoto = $photo;
        $extension = 'jpg';
        if (preg_match('/^data:image\/(png|jpe?g);base64,/', $rawPhoto, $matches)) {
            $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
            $rawPhoto = substr($rawPhoto,strpos($rawPhoto, ',') + 1);
        }
        $imageName = sprintf('attendance_%s_%s.%s',$employee->id,time(),$extension);
        $photoPath = 'attendance_photos/' . $imageName;
        $fullPath = storage_path('app/public/' . $photoPath);
        file_put_contents($fullPath,base64_decode(str_replace(' ', '+', $rawPhoto)));
        @chmod($fullPath, 0777);
        return $photoPath;
    }

    function verifyFaceRecognition($employee,string $photoPath) {

        $faceRecognitionApiUrl = env('FACERECOGNITION_API_URL');
        if (empty($faceRecognitionApiUrl)) {
            return;
        }
        $fullPath = storage_path('app/public/' . $photoPath);

        try {

            // Log::info('Face Recognition Image', [
            //     'employee_id' => $employee->id,
            //     'path'        => $fullPath,
            //     'mime'        => mime_content_type($fullPath),
            //     'size_kb'     => round(filesize($fullPath) / 1024, 2),
            //     'width'       => $image->width(),
            //     'height'      => $image->height(),
            // ]);
            $response = Http::timeout(15)
                ->attach(
                    'image',
                    fopen($fullPath, 'r'),
                    basename($fullPath),
                    [
                        'Content-Type' => mime_content_type($fullPath),
                    ]
                )
                ->post(
                    rtrim($faceRecognitionApiUrl, '/') . '/recognize',
                    [
                        'employeeId' => $employee->id,
                    ]
                );
            if (!$response->successful()) {
                deleteAttendancePhoto($fullPath);
                    throw new \Illuminate\Http\Exceptions\HttpResponseException(
                        response()->json(['message' => (string) $response->json('detail')], 502)
                    );
            }
            if ($detail = $response->json('detail')) {
                deleteAttendancePhoto($fullPath);
                    throw new \Illuminate\Http\Exceptions\HttpResponseException(
                        response()->json(['message' => $detail], 422)
                    );
            }
            $similarity = (float) ($response->json('similarity_percentage') ?? 0);
            if ($similarity < 50) {
                deleteAttendancePhoto($fullPath);
                    throw new \Illuminate\Http\Exceptions\HttpResponseException(
                        response()->json(['message' => 'Face is not recognized'], 422)
                    );
            }
            
            // $image = Image::make($fullPath);
            $jpgPath = preg_replace('/\.(png|jpeg|jpg)$/i', '.jpg', $fullPath);
            // if ($image->width() > 1280) {
            //     $image->orientate();
            //     $image->resize(1280, null, function ($constraint) {
            //         $constraint->aspectRatio();
            //         $constraint->upsize();
            //     });
            // }
            // $image->encode('jpg', 50)->save($fullPath);
            if ($jpgPath !== $fullPath) {
                unlink($fullPath);
            }

            // $fullPath = $jpgPath;
        } catch (\Throwable $e) {
            deleteAttendancePhoto($fullPath);
            Log::warning('Face recognition request failed', [
                'employee_id' => $employee->id,
                'photo_path' => $photoPath,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    function deleteAttendancePhoto(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    function createAttendanceLog($employee,$attendance,string $attendanceDate,?string $photoPath,array $data,string $type = 'check_in')
    {
        return AttendanceLog::create([
            'employee_id' => $employee->id,
            'attendance_id' => $attendance->id,
            'type' => $type,
            'fullname' => $attendance->fullname,
            'shift_name' => $attendance->shift_name,
            'photo' => $photoPath??null,
            'clock_datetime' => $data['date'],
            'clock_date' => $attendanceDate,
            'time' => Carbon::parse($data['date'])->format('H:i:s'),
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'radius' => $data['radius'] ?? null,
        ]);
    }

    function updateAttendanceCheckIn($attendance,?string $photoPath,array $data) {

        if (
            !$attendance->check_in ||
            Carbon::parse($data['date'])
                ->lt(Carbon::parse($attendance->check_in))
        ) {

            $attendance->update([
                'check_in' => $data['date'],
                'status' => 'present',
                'check_in_photo' => $photoPath??null,
                'check_in_latitude' => $data['latitude'] ?? null,
                'check_in_longitude' => $data['longitude'] ?? null,
                'check_in_radius' => $data['radius'] ?? null,
            ]);
        }
    }
