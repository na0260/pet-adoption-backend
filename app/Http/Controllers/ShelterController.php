<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
        }

    }

    public function updateShelter(Request $request)
    {
        $user = auth('api')->user();

        if ($user->role === 'user') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'old_password' => 'nullable|min:8|required_with:new_password',
            'new_password' => 'nullable|confirmed|min:8|required_with:old_password',
            'new_password_confirmation' => 'nullable|required_with:new_password',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|unique:shelters,phone,' . $user->shelter->id . '|digits:11',
            'uid' => 'nullable|string|unique:shelters,uid,' . $user->shelter->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $shelter = $user->shelter;

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();

            $userUpdated = false;
            $shelterUpdated = false;

            if (!empty($validatedData['name']) && $user->name !== $validatedData['name']) {
                $user->name = $validatedData['name'];
                $userUpdated = true;
            }

            if (!empty($validatedData['name']) && $shelter->name !== $validatedData['name']) {
                $shelter->name = $validatedData['name'];
                $shelterUpdated = true;
            }
            if (!empty($validatedData['address']) && $shelter->address !== $validatedData['address']) {
                $shelter->address = $validatedData['address'];
                $shelterUpdated = true;
            }
            if (!empty($validatedData['phone']) && $shelter->phone !== $validatedData['phone']) {
                $shelter->phone = $validatedData['phone'];
                $shelterUpdated = true;
            }
            if (!empty($validatedData['uid']) && $shelter->uid !== $validatedData['uid']) {
                $shelter->uid = $validatedData['uid'];
                $shelterUpdated = true;
            }

            if (!empty($validatedData['old_password']) && !empty($validatedData['new_password'])) {
                if (!Hash::check($validatedData['old_password'], $user->password)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Old password is incorrect'
                    ], 422);
                }
                $user->password = bcrypt($validatedData['new_password']);
                $userUpdated = true;
            }

            if ($userUpdated || $shelterUpdated) {
                $user->save();
                $shelter->save();
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Shelter updated successfully',
                    'data' => $shelter
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No changes were made'
                ], 422);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
