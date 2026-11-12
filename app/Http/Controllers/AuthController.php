<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Session;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function forgotPassword()
    {
        return view('login.password', ['mode' => 'request']);
    }

    public function sendResetLinkEmail(Request $request, PasswordResetService $passwordResetService)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $passwordResetService->sendResetLink($request->input('email'));

        return back()->with('status', 'If an account exists for that email address, a password reset link has been sent.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('login.password', [
            'mode' => 'reset',
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request, PasswordResetService $passwordResetService)
    {
        $credentials = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = $passwordResetService->reset($credentials);

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/login')->with('status', 'Your password has been reset. You can now sign in.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'This password reset link is invalid or has expired.']);
    }

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
            if ($token) {
                $authUser = Auth::user();
                if ($authUser->hasInactiveEmployee()) {
                    Auth::logout();

                    return response()->view('login.inactive', [], 403);
                }

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
                
                $authUser = auth()->user();
                $roles = $authUser->roles;

                if ($roles->isEmpty()) {
                    $role = Role::where('name', 'user')->first();
                    if ($role) {
                        $authUser->roles()->attach($role->id);
                    }
                }
                $employee = $authUser->employee;
                $isUser = $authUser->hasRole('user');

                session(['avatar' => $employee->personal->avatar ?? '']);
                session(['empId' => $employee->id ?? '']);

                if ($isUser) {
                    return redirect('/profile/personal/' . $employee->id);
                }

                return redirect('/');

            }
            return back()->with('LoginError', 'Email or password is not valid');
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
