<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ResolveVisitorApiUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request['user'];
        abort_unless($user instanceof User, 403, 'Forbidden');
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
