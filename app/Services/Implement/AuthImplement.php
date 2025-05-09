<?php

namespace App\Services\Implement;

use App\Models\Session;
use App\Models\User;
use App\Services\AuthService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use function App\Helpers\getUserTalentaByEmail;

class AuthImplement implements AuthService
{
    function login($request, $deviceId, $device)
    {
        try {

            // cehcking account in academy.mhis.link
            $isExist = $this->checkAcademyAccount($request['email']);
            if (!$isExist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Username is not registered in Academy System !',
                    "logoutAll" => false,
                ], 401);
            }

            //get token login to system
            $token = Auth::guard('api')->attempt($request);
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Username Or Password wrong !',
                    "logoutAll" => false,
                ], 401);
            }

            //checking user id talenta
            $user = Auth::guard('api')->user();
            if (!isset($user->user_id_talenta)) {
                $talentaUser =  getUserTalentaByEmail($user->email);
                $localUser = User::where('id', $user->id)->first();
                $localUser->user_id_talenta = $talentaUser->user_id;
                $localUser->save();
            }

            //insert token to Session table
            $sessions = Session::where('user_id', $user->id)->where('device', '!=', 'web')->get();
            if (count($sessions) >= 3) {
                Session::where('user_id', $user->id)->where('device', '!=', 'web')->delete();
            }
            $session = new Session();
            $session->token = 'bearer ' . $token;
            $session->user_id = $user->id;
            $session->device = $device;
            $session->device_id = $deviceId;
            $session->login_date = date("Y-m-d H:i:s");
            $session->save();

            return response()->json([
                'status' => 'success',
                'user' => User::where('id', $user->id)->first(),
                'authorization' => [
                    'token' => 'bearer ' . $token,
                    'type' => 'bearer',
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage(), "logoutAll" => true,], 500);
        }
    }
    function register($request) {}
    function changePassword($request)
    {
        try {
            $password = $request->oldPassword;
            $user = User::where('email', $request['user']['email'])->first();
            $isValidPassword =  Hash::check($password, $user->password);
            if (!$isValidPassword) {
                return response()->json(["message" => "Password is wrong"], 400);
            }
            $user->password = Hash::make($request->newPassword);
            $user->save();
            return response()->json(["message" => "success"], 200);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function refresh($request)
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'Authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
    function logout($request)
    {
        try {
            $token = $request->header('Authorization');
            Session::where('token', $token)->delete();
            Auth::logout();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }

    function checkAcademyAccount($email)
    {
        $url = "http://academy.mhis.link";
        $client =  new Client([
            'base_uri' => $url
        ]);
        $method     = 'GET';
        $path = "/user/check/" . $email;
        $queryParam = "";
        $response = $client->request(
            $method,
            $path . $queryParam,
            [
                'headers'   => [
                    "Content-Type" => "application/json"
                ]
            ]
        );
        return json_decode($response->getBody());
    }
}
