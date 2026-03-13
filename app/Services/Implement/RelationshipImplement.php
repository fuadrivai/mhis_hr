<?php

namespace App\Services\Implement;

use App\Models\Relationship;
use App\Services\RelationshipService;

class RelationshipImplement implements RelationshipService
{
    function get()
    {
        try {
            return Relationship::all();
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
    function show($id)
    {

        try {
            return Relationship::find($id);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
    function post($request)
    {
        try {
            $religion = new Relationship();
            $religion->name = $request['name'];
            $religion->save();
            return $religion;
        } catch (\Throwable $th) {
             throw new \Exception($th->getMessage());
        }
    }
    function put($id, $request)
    {
        try {
            Relationship::where('id', $id)->update([
                "name" => $request["name"],
            ]);
            return Relationship::find($id);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
    function delete($id) {}
}
