<?php

namespace App\Helpers;

use Carbon\Carbon;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

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

function sendMessage($deviceToken, $d)
{
    $credential = new ServiceAccountCredentials(
        "https://www.googleapis.com/auth/firebase.messaging",
        json_decode(file_get_contents(storage_path('mhis-hub-73a9a67cee5e.json')), true)
    );
    $token = $credential->fetchAuthToken(HttpHandlerFactory::build());
    $ch = curl_init("https://fcm.googleapis.com/v1/projects/mhis-hub/messages:send");

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token['access_token']
    ]);
    $postFields = json_encode([
        "message" => [
            "token" => $deviceToken,
            "notification" => [
                "title" => $d['title'],
                "body" => $d['body'],
                "image" => $d['image'] ?? "",
            ],
            "apns" => [
                "payload" => [
                    "aps" => [
                        "alert" => [
                            "title" => $d['title'],
                            "body" => $d['body'],
                            "image" => $d['image'] ?? "",
                        ],
                        "badge" => 1
                    ]
                ]
            ]

        ]
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "post");
    curl_exec($ch);
    curl_close($ch);
    // echo $response;
    // return response()->json(json_decode($postFields));
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

function resolveAttendanceDate($shift, $clock)
{
    $clockTime = Carbon::parse($clock);
    $scheduleOut = Carbon::parse($shift->schedule_out);

    // NOT Overnight SHIFT
    if (!$shift->is_overnight) {
        return [
            'attendance_date' => $clockTime->toDateString(),
            'schedule_in' => $shift->schedule_in,
            'schedule_out' => $shift->schedule_out,
        ];
    }

    // OVERNIGHT SHIFT
    if ($clockTime->format('H:i:s') < $scheduleOut->format('H:i:s')) {
        return [
            'attendance_date' => $clockTime->copy()->subDay()->toDateString(),
            'schedule_in' => $shift->schedule_in,
            'schedule_out' => $shift->schedule_out,
        ];
    }

    // Jam 19:00–23:59 → shift hari ini
    return [
        'attendance_date' => $clockTime->toDateString(),
        'schedule_in' => $shift->schedule_in,
        'schedule_out' => $shift->schedule_out,
    ];
}
