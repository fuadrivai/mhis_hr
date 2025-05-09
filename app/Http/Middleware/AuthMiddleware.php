<?php

namespace App\Http\Middleware;

use App\Models\Session;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!isset($token)) {
            return response()->json(["message" => "Forbidden"], 403);
        } else {
            $session = Session::where('token', $token)->first();
            if (!isset($session)) {
                return response()->json(["message" => "Forbidden"], 403);
            } else {
                $user = User::find($session->user_id);
                $request['user'] = $user;
                return $next($request);
            }
        }
    }
}
