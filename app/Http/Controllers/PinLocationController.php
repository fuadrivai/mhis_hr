<?php

namespace App\Http\Controllers;

use App\Models\PinLocation;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;

class PinLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $locations = PinLocation::with(['branch'])->get();
        return view('location.index', [
            "title" => "Location",
            "locations" => $locations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $branches = Branch::all();

        return view('location.form', [
            "title" => "Location Form",
            "branches" => $branches,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePinLocationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $employees = $request['employees'];
        $employeesJson = json_decode($employees);
        $loc = new PinLocation();
        $loc->name = $request->name;
        $loc->latitude = $request->latitude;
        $loc->longitude = $request->longitude;
        $loc->radius = $request->radius;
        $loc->total = count($employeesJson);
        $loc->description = $request->description;
        $loc->branch_id = $request->branch['id'];
        $loc->save();

        for ($i = 0; $i < count($employeesJson); $i++) {
            $employee = $employeesJson[$i];
            Employee::where('id', $employee->id)->update([
                "pin_location_id" => $request['id']
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PinLocation  $pinLocation
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $location = PinLocation::with(['employees' => function ($q) {
            $q->with(['personal', 'employment']);
        }])->find($request->id);
        return response()->json($location);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PinLocation  $pinLocation
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $branches = Branch::all();
        $location = PinLocation::with('employees')->find($id);

        return view('location.form', [
            "title" => "Location Form",
            "branches" => $branches,
            "location" => $location,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePinLocationRequest  $request
     * @param  \App\Models\PinLocation  $pinLocation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $employees = $request['employees'];
        $employeesJson = json_decode($employees);
        PinLocation::where('id', $request['id'])->update([
            'name' => $request['name'],
            'description' => $request['description'],
            'latitude' => $request['latitude'],
            'longitude' => $request['longitude'],
            'radius' => $request['radius'],
            'total' => count($employeesJson),
            'branch_id' => $request['branch']['id'],
            'branch_id' => $request['branch']['id'],
        ]);
        Employee::where('pin_location_id', $request['id'])->update([
            "pin_location_id" => null
        ]);
        for ($i = 0; $i < count($employeesJson); $i++) {
            $employee = $employeesJson[$i];
            Employee::where('id', $employee->id)->update([
                "pin_location_id" => $request['id']
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PinLocation  $pinLocation
     * @return \Illuminate\Http\Response
     */
    public function destroy(PinLocation $pinLocation)
    {
        //
    }
}
