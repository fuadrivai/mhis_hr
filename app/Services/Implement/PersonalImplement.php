<?php

namespace App\Services\Implement;

use App\Models\Personal;
use App\Services\PersonalService;
use Carbon\Carbon;

class PersonalImplement implements PersonalService
{
    function get($request) {}
    function show($id) {}
    function post($request) {}
    function put($request)
    {
        try {
            $personal = Personal::findOrFail($request['id']);
            $request['birth_date'] = !empty($request['birth_date'])
                ? Carbon::parse($request['birth_date'])->format('Y-m-d')
                : null;

            $request['expired_date_identity_id'] = !empty($request['expired_date_identity_id'])
                ? Carbon::parse($request['expired_date_identity_id'])->format('Y-m-d')
                : null;
            $request['gendre'] = $request['gendre'] == "male" ? 1 : 2;

            // ✅ Set fullname otomatis
            $request['fullname'] = $request['first_name'] . ' ' . $request['last_name'];
            $personal->update($request);

            return response()->json(["message" => "Data berhasil diubah", "data" => $personal], 200);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
