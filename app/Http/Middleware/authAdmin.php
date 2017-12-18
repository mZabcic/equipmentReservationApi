<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;

class authAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->role_id != 1) {
            return response()->json(['error'=>'No admin rights'], 403);
        }
        return $next($request);
    }
}