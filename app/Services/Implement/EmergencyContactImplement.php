<?php

namespace App\Services\Implement;

use App\Models\EmergencyContact;
use App\Models\Family;
use App\Services\EmergencyContactService;

class EmergencyContactImplement implements EmergencyContactService
{
    function get()
    {
        try {
            return EmergencyContact::all();
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
    function show($id)
    {
        try {
            return EmergencyContact::find($id);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
    function post($request)
    {
        try {
            $econ = new EmergencyContact();
            $econ->name = $request['fullname'];
            $econ->personal_id = $request['personal_id'];
            $econ->relation_ship_id = $request['relation_ship_id'];
            $econ->mobile_number = $request['mobile_number'] ?? null;
            
            $econ->save();

            return $econ;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
    function put($id,$request)
    {
        try {
            $econ = EmergencyContact::findOrFail($request['id']);
            if (! $econ) {
                throw new \Exception("data not found");
            }

            $econ->update([
                'name' => $request['fullname'] ?? $econ->fullname,
                'relation_ship_id' => $request['relation_ship_id'] ?? $econ->relation_ship_id,
                'religion_id' => $request['religion_id'] ?? $econ->religion_id,
                
            ]);

            return $econ;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
    function delete($id) {
        try {
            $econ = EmergencyContact::findOrFail($id);
            if (!$econ) {
                throw new \Exception("data not found");
            }
            $econ->delete();
            return true;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
}
