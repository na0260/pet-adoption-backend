<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken() || $request->cookie('token');
            if ($token) {
                return response()->json(['status' => "Failed", 'message' => 'User already logged in'], 400);
            } else {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users|max:255',
                    'password' => 'required|confirmed|min:8',
                    'password_confirmation' => 'required'
                ]);

                if ($validator->fails()) {
                    return response()->json($validator->errors()->toJson(), 400);
                }

                $user = User::create([
                    'name' => request()->name,
                    'email' => request()->email,
                    'password' => bcrypt(request()->password),
                ]);

                return response()->json(['status' => "Success", 'data' => $user], 201);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => "Failed", 'message' => $e->getMessage()], 400);
        }
    }

    public function login(Request $request)
    {
        $token = $request->bearerToken() || $request->cookie('token');
        if ($token) {
            return response()->json(['status' => "Failed", 'message' => 'User already logged in'], 400);
        }else{
            try {
                $credentials = $request->only('email', 'password');
                try {
                    if (!$token = auth('api')->attempt($credentials)) {
                        return response()->json(['status' => "Failed", 'message' => 'Invalid Credential'], 401);
                    }
                } catch (JWTException $e) {
                    return response()->json(['status' => "Failed", 'message' => $e->getMessage()], 500);
                }

                $expiration = 60 * 24;

                $cookie = Cookie::make('token', $token, $expiration, '/', null, true, true, false, 'Strict');


                return response()->json(['status' => "Success", 'token' => $token], 200)->cookie($cookie);
            } catch (\Exception $e) {
                return response()->json(['status' => "Error", 'message' => $e->getMessage()], 401);
            }
        }
    }

    public function me(): JsonResponse
    {
        $user = auth('api')->user();
        return response()->json(['status' => "Success", 'data' => [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]], 200);
    }

    public function logout()
    {
        try {
            auth('api')->logout();
            $cookie = Cookie::forget('token');
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Successfully logged out'])->cookie($cookie);
    }
}
