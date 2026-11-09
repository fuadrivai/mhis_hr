<?php

namespace App\Services;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function sendResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function reset(array $credentials): string
    {
        return Password::reset($credentials, function ($user) use ($credentials) {
            $user->forceFill([
                'password' => Hash::make($credentials['password']),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });
    }
}
