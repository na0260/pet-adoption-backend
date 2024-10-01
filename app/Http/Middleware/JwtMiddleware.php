<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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
            $user = JWTAuth::authenticate();
            $response = $next($request);

            return $response->header('user-id', $user->id);
        } catch (TokenExpiredException $e) {
            try {
                $refreshedToken = JWTAuth::refresh($token);
                JWTAuth::setToken($refreshedToken);
                $user = JWTAuth::authenticate();

                $response = $next($request);
                $expiration = 60 * 24;

                $cookie = Cookie::make('token', $refreshedToken, $expiration, '/', null, true, true, false, 'Strict');

                return $response->withCookie($cookie)->header('user-id', $user->id);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Token expired, please log in again.'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['error' => 'Something went wrong'], 500);
    }
}
