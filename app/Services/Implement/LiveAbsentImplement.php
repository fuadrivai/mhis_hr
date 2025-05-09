<?php

namespace App\Services\Implement;

use App\Models\Employee;
use App\Models\LiveAbsent;
use App\Services\LiveAbsentService;
use Carbon\Carbon;
use GuzzleHttp\Client;

use function App\Helpers\distance;

class LiveAbsentImplement implements LiveAbsentService
{
    function get($request) {}
    function filterByUser($request)
    {
        try {
            $absent = LiveAbsent::where('user_id', $request['user']['id'])
                ->whereMonth('clock_time', '=', $request['month'])
                ->whereYear('clock_time', '=', $request['year'])
                ->orderBy('clock_time', "DESC")
                ->with(['user', 'pin_locations']);

            if ($request['absent_type']) {
                $absent->where('absent', $request['absent_type'] ?? 1);
            }
            if ($request['location']) {
                $absent->where('pin_locations_id', $request['location']);
            }
            return response()->json($absent->get());
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id) {}
    function post($request)
    {
        try {
            $employee = Employee::where('user_id', $request['user']['id'])->with('pin_location')->first();

            $distanceInKM = distance($request['latitude'], $request['longitude'], $employee->pin_location->latitude, $employee->pin_location->longitude, "K");
            $distance = number_format((float)($distanceInKM * 1000), 2, '.', '');
            if ($distance > (float)$employee->pin_location->radius) {
                return response()->json(["message" => "Out of range from " . $employee->pin_location->name . " " . $distance . " meters"], 400);
            }
            $absent = new LiveAbsent();
            $absent->user_id = $request['user']['id'];
            $absent->fullname = $request['user']['name'];
            $absent->pin_locations_id = $employee->pin_location->id;
            $absent->location_name = $employee->pin_location->name;
            $absent->coordinate = $employee->pin_location->latitude . " , " . $employee->pin_location->longitude;
            $absent->radius = (float)$employee->pin_location->radius;
            $absent->distance = (float)$distance;
            $absent->note = $request['note'];
            $absent->clock_time = $request['clock_time'];
            $absent->latitude = $request['latitude'];
            $absent->longitude = $request['longitude'];
            $absent->absent_type = 1; // solat asar
            $absent->event_type = "clock_in";
            $absent->save();
            return response()->json(LiveAbsent::find($absent->id));
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($id, $request) {}
    function delete($id) {}

    function getCity($city)
    {
        try {
            $client =  new Client([
                'base_uri' => env('SCHEDULE_SHOLAT_BASE_URL')
            ]);
            $method     = 'GET';
            $path = "/v2/sholat/kota/cari/$city";
            $queryParam = "";
            $response = $client->request(
                $method,
                $path . $queryParam,
                [
                    'headers'   => [
                        "Content-Type" => "application/json"
                    ]
                ]
            );
            $dataCity = json_decode($response->getBody())->data;
            if (count($dataCity) == 0) {
                return response()->json(["message" => "location is not detected"], 404);
            }

            $client2 =  new Client([
                'base_uri' => env('SCHEDULE_SHOLAT_BASE_URL')
            ]);
            $method2     = 'GET';
            $currDate = Carbon::now()->format('Y-m-d');
            $path2 = "/v2/sholat/jadwal/" . $dataCity[0]->id . "/$currDate";
            $queryParam2 = "";
            $response2 = $client2->request(
                $method2,
                $path2 . $queryParam2,
                [
                    'headers'   => [
                        "Content-Type" => "application/json"
                    ]
                ]
            );
            return json_decode($response2->getBody())->data;
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), $th->getCode());
        }
    }
}
