<?php

namespace App\Services\Implement;

use App\Models\Payslip;
use App\Models\Session;
use App\Models\User;
use App\Services\PayslipService;

use function App\Helpers\sendMessage;

class PayslipImplement implements PayslipService
{
    function get($request)
    {
        try {
            $paySlip = Payslip::orderBy('periode', 'desc');
            if ($request['email']) {
                $paySlip->where('email', $request['email']);
            }
            if ($request['month'] && $request['year']) {
                $periode = $request['year'] . "-" . $request['month'] . "-01";
                $paySlip->where('periode', $periode);
            }
            if ($request['year']) {
                $paySlip->whereYear('periode', $request['year']);
            }
            $paymentSlip = $paySlip->paginate($request->perpage ?? 12)->withQueryString();
            return response()->json($paymentSlip);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function show($id) {}
    function post($request)
    {
        try {
            $payslip = new Payslip();
            $payslip->link = $request->link;
            $payslip->periode = $request->periode;
            $payslip->email = $request->email;
            $payslip->save();

            $user = User::where('email', $request->email)->first();
            if(isset($user)){
                $device_token = Session::select('device_id')->where('user_id', $user->id)->get();
                for ($i = 0; $i < count($device_token); $i++) {
                    $data = [
                        "title" => "Payment Slip Periode $request->periode",
                        "body" => "Berikut kami kirimkan Slip gaji atas nama $user->name",
                    ];
                    sendMessage($device_token[$i]->device_id, $data);
                }
            }
            
            return response()->json($payslip);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($request) {}
    function delete($id) {}
}
