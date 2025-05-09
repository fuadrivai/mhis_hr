<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Illuminate\Http\Request;

class PushNotificationApiController extends Controller
{

    // private $deviceTokens = ['dUHQgsFjT72piy52r1usU4:APA91bGxL9VsCFPcyfR2Ad3c4j2L7JkXSzfpiUjCaeAA6ooPabjV3vFMHdqD6cqSfWY54phjmgnUznY6MRbAm3GuzTqm9Uvooq5wfOzCxXcVBC1isiFdbFjJY6cwnXGjbreY0gpQqvfw'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendMessage($deviceToken, $d)
    {
        $credential = new ServiceAccountCredentials(
            "https://www.googleapis.com/auth/firebase.messaging",
            json_decode(file_get_contents(storage_path('mhis-employee-firebase-adminsdk-hs9hc-a6460bb0e8.json')), true)
        );
        $token = $credential->fetchAuthToken(HttpHandlerFactory::build());
        $ch = curl_init("https://fcm.googleapis.com/v1/projects/mhis-employee/messages:send");

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
                    "image" => $d['image']
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "alert" => [
                                "title" => $d['title'],
                                "body" => $d['body']
                            ],
                            "badge" => 1
                        ]
                    ]
                ]

            ]
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "post");
        $response = curl_exec($ch);
        curl_close($ch);
        echo $response;
        return response()->json(json_decode($postFields));
    }
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
