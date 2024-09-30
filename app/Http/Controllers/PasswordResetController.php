<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOTP;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    public function sendOTP(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()
            ], 400);
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
