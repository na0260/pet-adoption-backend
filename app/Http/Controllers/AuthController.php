<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $token = $request->bearerToken() ?: $request->cookie('token');
            if ($token) {
                return response()->json(['status'=>"Failed",'message'=>'User already logged in'], 400);
            }else{
                $validator = Validator::make(request()->all(), [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users|max:255',
                    'password' => 'required|confirmed|min:8',
                    'password_confirmation' => 'required'
                ]);

                if($validator->fails()){
                    return response()->json($validator->errors()->toJson(), 400);
                }

                $user = new User();
                $user->name = request()->name;
                $user->email = request()->email;
                $user->password = bcrypt(request()->password);
                $user->save();

                return response()->json(['status'=>"Success",'data'=>$user], 201);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>"Failed",'message'=>$e], 400);
        }
    }

    public function login()
    {
        try {
            $credentials = request(['email', 'password']);
            try {
                if (! $token = auth('api')->attempt($credentials)) {
                    return response()->json(['status'=>"Failed",'message'=>'Unauthorized'], 401);
                }
            }catch (JWTException $e){
                return response()->json(['status'=>"Failed",'message'=>$e], 500);
            }

            $expiration = 60 * 24; // 60 minutes * 24 hours

            $cookie = Cookie::make('token', $token, $expiration, '/', null, true, true); // Secure, HttpOnly


            return response()->json(['status'=>"Success",'token'=>$token], 200,)->cookie($cookie);
        }catch(\Exception $e){
            return response()->json(['status'=>"Error",'message'=>$e], 401);
        }
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        try {
            auth('api')->logout();
            $cookie = Cookie::forget('token');
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not log out, please try again later'], 500);
        }

        return response()->json(['message' => 'Successfully logged out'])->cookie($cookie);
    }
}
