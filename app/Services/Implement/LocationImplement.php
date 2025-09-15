<?php

namespace App\Services\Implement;

use App\Models\Employee;
use App\Models\Location;
use App\Models\LocationDetail;
use App\Services\LocationService;
use Illuminate\Support\Facades\DB;

class LocationImplement implements LocationService
{
    function get()
    {
        $locations = Location::all();

        return $locations;
    }
    function show($id)
    {
        try {
            $location = Location::with([
                'details',
                'employees.personal',
                'employees.employment'
            ])->find($id);
            return $location;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function post($request)
    {
        try {
            $location = null;
            DB::transaction(function () use ($request, &$location) {
                $location = new Location();
                $location->name = $request['name'];
                $location->need_location = $request['need_location'] ? 1 : 0;
                $location->save();

                $details = [];
                foreach ($request['details'] as $d) {
                    $details[] = [
                        'location_id'   => $location->id,
                        'name'      => $d['name'],
                        'address'    => $d['address'],
                        'description'    => $d['description'],
                        'latitude'           => $d['latitude'],
                        'longitude'        => $d['longitude'],
                        'radius'        => $d['radius'],
                    ];
                }
                if (!empty($details)) {
                    LocationDetail::insert($details);
                }

                $employeeIds = collect($request['employees'] ?? [])->pluck('id');
                if ($employeeIds->isNotEmpty()) {
                    Employee::whereIn('id', $employeeIds)->update(['location_id' => $location->id]);
                }
            });
            return $location;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($request)
    {
        try {
            DB::transaction(function () use ($request) {
                $needLocation = $request['need_location'] == "true" ? 1 : 0;
                Location::where('id', $request['id'])->update([
                    "name" => $request["name"],
                    "need_location" => $needLocation
                ]);
                LocationDetail::where('location_id', $request['id'])->delete();
                if ($needLocation == 1) {
                    $details = [];
                    foreach ($request['details'] as $d) {
                        $details[] = [
                            'location_id'   => $request['id'],
                            'name'      => $d['name'],
                            'address'    => $d['address'],
                            'description'    => $d['description'],
                            'latitude'           => $d['latitude'],
                            'longitude'        => $d['longitude'],
                            'radius'        => $d['radius'],
                        ];
                    }
                    if (!empty($details)) {
                        LocationDetail::insert($details);
                    }
                }

                $newEmployeeIds = collect($request['employees'] ?? [])->pluck('id')->toArray();
                $oldEmployeeIds = Employee::where('location_id', $request['id'])->pluck('id')->toArray();

                $toDetach = array_diff($oldEmployeeIds, $newEmployeeIds);
                $toAttach = array_diff($newEmployeeIds, $oldEmployeeIds);

                if (!empty($toDetach)) {
                    Employee::whereIn('id', $toDetach)->update(['location_id' => null]);
                }
                if (!empty($toAttach)) {
                    Employee::whereIn('id', $toAttach)->update(['location_id' => $request['id']]);
                }
            });
            $location = Location::with('details')->find($request['id']);
            return $location;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function delete($id) {}
    function filterEmployee()
    {
        $employees = Employee::whereNull('location_id');
        return $employees;
    }
}
