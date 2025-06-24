<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckValidJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $incomingToken = JWTAuth::getToken()->get();

        if ($user && $user->current_jwt_token !== $incomingToken) {
            return response()->json(['message' => 'You have been logged out from this device.'], 401);
        }
        return $next($request);
    }
}
