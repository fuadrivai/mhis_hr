<?php

namespace App\Services\Implement;

use App\Models\Family;
use App\Services\FamilyService;

class FamilyImplement implements FamilyService
{
    function get()
    {
        try {
            return Family::all();
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
    function show($id)
    {
        try {
            return Family::find($id);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
    function post($request)
    {
        try {
            $family = new Family();
            $family->fullname = $request['fullname'];
            $family->personal_id = $request['personal_id'];
            $family->relation_ship_id = $request['relation_ship_id'];
            $family->religion_id = $request['religion_id'];
            $family->mobile_number = $request['mobile_number'] ?? null;
            $family->address = $request['address'] ?? null;
            $family->id_number = $request['id_number'] ?? null;
            $family->gendre = $request['gendre'];
            $family->marital_status = $request['marital_status'] ?? null;
            $family->birth_date = $request['birth_date'];
            $family->job = $request['job'] ?? null;
            $family->save();

            return $family;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
    function put($request)
    {
        try {
            $family = Family::findOrFail($request['id']);
            if (! $family) {
                throw new \Exception("data not found");
            }

            $family->update([
                'fullname' => $request['fullname'] ?? $family->fullname,
                'personal_id' => $request['personal_id'] ?? $family->personal_id,
                'relation_ship_id' => $request['relation_ship_id'] ?? $family->relation_ship_id,
                'religion_id' => $request['religion_id'] ?? $family->religion_id,
                'mobile_number' => $request['mobile_number'] ?? $family->mobile_number,
                'address' => $request['address'] ?? $family->address,
                'id_number' => $request['id_number'] ?? $family->id_number,
                'gendre' => $request['gendre'] ?? $family->gendre,
                'marital_status' => $request['marital_status'] ?? $family->marital_status,
                'birth_date' => $request['birth_date'] ?? $family->birth_date,
                'job' => $request['job'] ?? $family->job,
            ]);

            return $family;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
    function delete($id) {
        try {
            $family = Family::findOrFail($id);
            if (!$family) {
                throw new \Exception("data not found");
            }
            $family->delete();
            return true;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
}
