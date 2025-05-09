<?php

namespace App\Services\Implement;

use App\Models\PinLocation;
use App\Services\PinLocationService;


class PinLocationImplement implements PinLocationService
{
    function get($request)
    {
        try {
            $locations = PinLocation::with(['branch'])->get();
            return response()->json($locations);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id)
    {
        try {
            $location = PinLocation::with('branch')->find($id);
            return response()->json($location);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function post($request)
    {
        try {
            $location = new PinLocation();
            $location->latitude = $request['latitude'];
            $location->longitude = $request['longitude'];
            $location->branch_id = $request['branch']['id'];
            $location->radius = $request['radius'];
            $location->name = $request['name'];
            $location->description = $request['description'];
            $location->save();

            $postLocation = PinLocation::with(['branch'])->find($location->id);
            return response()->json($postLocation);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($id, $request)
    {
        try {
            PinLocation::where('id', $id)->update([
                "name" => $request["name"],
                "description" => $request["description"],
                "latitude" => $request["latitude"],
                "longitude" => $request["longitude"],
                "radius" => $request["radius"],
                "branch_id" => $request["branch"]["id"],
            ]);
            $postLocation = PinLocation::with(['branch'])->find($id);
            return response()->json($postLocation);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id)
    {
        try {
            PinLocation::where('id', $id)->delete();
            return response()->json(true);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
}
