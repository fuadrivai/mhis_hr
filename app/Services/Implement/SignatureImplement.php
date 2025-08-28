<?php

namespace App\Services\Implement;

use App\Models\Employee;
use App\Models\Signature;
use App\Services\SignatureService;
use Illuminate\Support\Facades\Auth;

class SignatureImplement implements SignatureService
{
    function get()
    {
        try {
            $signatures = Signature::all();
            return response()->json($signatures);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function show($id)
    {
        try {
            $signature = Signature::find($id);
            return response()->json($signature);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function post($request)
    {
        try {
            $signature = new Signature();
            $signature->title = $request['title'];
            $signature->sign_date = $request['sign_date'];
            $signature->description = $request['description'];

            $user = Auth::guard('api')->user();
            $signature->created_by = $user->id;

            $employee = Employee::where('user_id', $user->id)->with('employment')->first();
            $code = $this->sgnCode($employee->employment->employee_id);
            $signature->code = $code;
            $signature->save();
            return response()->json($signature);
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            // Log::info('Form Data:', $th->getMessage());
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }

    private function sgnCode()
    {
        $code = "SGN";
        $currDate = date("ymd");
        $n = 0;
        $n2 = "";
        $models = Signature::where('code', 'LIKE', "%{$currDate}%")->orderBy('code', 'desc')->take(1)->get();
        if (count($models) != 0) {
            $n2 = substr($models[0]->code, -4);
            $n2 = str_pad($n2 + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $n2 = str_pad($n + 1, 4, 0, STR_PAD_LEFT);
        }

        $fullCode = $code . "" . $currDate . "" . $n2;
        return $fullCode;
    }
    function put($id, $request) {}
    function delete($id) {}
}
