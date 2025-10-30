<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('login.login');
    }

    public function authenticate(Request $request)
    {

        try {
            $credentials = $request->validate([
                'email' => ['required'],
                'password' => ['required'],
            ]);
            $token = Auth::attempt($credentials);
            if (isset($token)) {
                $request->session()->regenerate();
                //get JWT token login to system
                $jwtToken = Auth::guard('api')->attempt($credentials);
                if (!$jwtToken) {
                    return back()->with('LoginError', 'Incorrect username or password ');
                }
                $user = Auth::guard('api')->user();
                $sessions = Session::where('user_id', $user->id)->where('device', 'web')->get();
                if (count($sessions) >= 2) {
                    Session::where('user_id', $user->id)->where('device', 'web')->delete();
                }

                $session = new Session();
                $session->token = 'bearer ' . $jwtToken;
                $session->user_id = $user->id;
                $session->device = "web";
                $session->device_id = "";
                $session->login_date = date("Y-m-d H:i:s");
                $session->save();

                session(['Authorization' => $session->token]);
                $isUser = auth()->user()->hasRole('user');
                if (!$isUser) {
                    return redirect()->intended('/');
                }else{
                    return redirect()->intended('/internal-document');
                }

            }
            return back()->with('LoginError', 'Email is not valid');
        } catch (\Throwable $th) {
            return back()->with('LoginError', $th->getMessage());
        }
    }

    public function logout(Request $request)
    {
        $token = session('Authorization');
        Session::where('token', $token)->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
