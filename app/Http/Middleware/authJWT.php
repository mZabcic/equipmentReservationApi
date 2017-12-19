<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Auth;

class authJWT
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
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {

            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['error'=>'Token is Invalid'], 401);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                $token = JWTAuth::refresh( Auth::guard()->user());
                return response()->json(['token' => $token, 'error'=>'Token is Expired'], 410);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\JWTException ){
                return response()->json(['error'=>'No token recived'], 402);
            } else {
                return response()->json(['error'=>'Something is wrong'], 500);
            }
        }
        return $next($request);
    }
}