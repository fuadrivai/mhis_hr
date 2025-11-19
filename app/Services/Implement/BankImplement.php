<?php

namespace App\Services\Implement;

use App\Models\Bank;
use App\Services\BankService;

class BankImplement implements BankService
{
    function get()
    {
        try {
            return Bank::all();
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function show($id)
    {
        try {
            $bank = Bank::find($id);
            return response()->json($bank);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function post($request)
    {
        try {
            $bank = new Bank();
            $bank->name = $request['name'];
            $bank->save();
            return response()->json($bank);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($id, $request)
    {
        try {
            Bank::where('id', $id)->update([
                "name" => $request["name"],
            ]);
            $bank = Bank::find($id);
            return response()->json($bank);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function delete($id) {}
}
