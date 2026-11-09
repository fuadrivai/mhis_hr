<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{

    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'device' => 'required|string',
            'device_id' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        return $this->authService->login($credentials, $request['device_id'], $request['device']);
    }

    public function sendResetLinkEmail(Request $request, PasswordResetService $passwordResetService)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $passwordResetService->sendResetLink($request->input('email'));

        return response()->json([
            'message' => 'If an account exists for that email address, a password reset link has been sent.',
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
            return response()->json(['message' => 'Your password has been reset successfully.']);
        }

        return response()->json([
            'message' => 'This password reset link is invalid or has expired.',
        ], 422);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function changePassword(Request $request)
    {
        if (empty($request['oldPassword']) || empty($request['newPassword'])) {
            return response()->json(["message" => "Old Password or New Password cannot be empty !"], 400);
        }
        return $this->authService->changePassword($request);
    }

    public function logout(Request $request)
    {
        return $this->authService->logout($request);
    }

    public function refresh(Request $request)
    {
        return $this->authService->logout($request);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
