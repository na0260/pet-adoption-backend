<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOTP;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    public function sendOTP(Request $request): JsonResponse
    {
        $token = $request->bearerToken() || $request->cookie('token');
        if ($token) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User already logged in'
            ], 400);
        }else{
            $validated = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validated->errors()
                ], 422);
            }

            $otp = (string) rand(100000, 999999);
            $email = $request->email;

            $user = User::where('email', $email)->first();

            if ($user) {
                Mail::to($email)->send(new PasswordResetOTP($otp, $email));
                $user->otp = $otp;
                $user->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'OTP sent to your mail successfully',
                ]);
            }
            return response()->json([
                'status' => 'failed',
                'message' => 'User not found'
            ], 404);
        }
    }

    public function validateOTP(Request $request): JsonResponse
    {
        $token = $request->bearerToken() || $request->cookie('token');
        if ($token) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User already logged in'
            ], 400);
        }else{
            $validated = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|digits:6'
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validated->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if ($user->otp == $request->otp) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'OTP validated successfully'
                ]);
            }

            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid OTP'
            ], 400);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $token = $request->bearerToken() || $request->cookie('token');
        if ($token) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User already logged in'
            ], 400);
        }else{
            $validated = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|digits:6',
                'password' => 'required|confirmed|min:8',
                'password_confirmation' => 'required'
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validated->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();
            if ($user->otp != $request->otp) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid OTP'
                ], 400);
            }else{
                $otp = (string) rand(100000, 999999);
                $user->otp = $otp;
                $user->password = bcrypt($request->password);
                $user->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Password reset successfully'
                ]);
            }
        }
    }
}
