<?php

namespace App\Services\Implement;

use App\Models\Location;
use App\Services\LocationService;

class LocationImplement implements LocationService
{
    function get()
    {
        $locations = Location::all();
        return $locations;
    }
    function show($id)
    {
        $location = Location::find($id);
        return $location;
    }
    function post($request) {}
    function put($request) {}
    function delete($id) {}
}
