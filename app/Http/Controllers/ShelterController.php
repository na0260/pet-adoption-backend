<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShelterController extends Controller
{
    public function createShelter(Request $request)
    {
        $token = $request->bearerToken() || $request->cookie('token');
        if ($token) {
            return response()->json(['status' => "Failed", 'message' => 'User already logged in'], 400);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'password' => 'required|confirmed|min:8',
                'password_confirmation' => 'required',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|unique:shelters,phone|digits:11',
                'uid' => 'required|string|unique:shelters,uid|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();
            try {
                $validatedData = $validator->validated();
                $user = User::create([
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'password' => bcrypt(request()->password),
                    'role' => 'shelter'
                ]);

                $shelter = $user->shelter()->create([
                    'name' => $validatedData['name'],
                    'address' => $validatedData['address'],
                    'phone' => $validatedData['phone'],
                    'uid' => $validatedData['uid'],
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Shelter created successfully',
                    'data' => $shelter
                ], 201);
            }catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
        }

    }
}
