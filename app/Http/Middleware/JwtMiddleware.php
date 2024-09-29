<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?: $request->cookie('token');

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            JWTAuth::setToken($token);
            JWTAuth::authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
        return $next($request);
    }
}
