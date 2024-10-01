<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PetController extends Controller
{
    public function addPet(Request $request)
    {
        $user = auth('api')->user();

        if ($user->role !== 'shelter') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'breed' => 'nullable|string|max:255',
            'age' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female',
            'vaccinated' => 'required|in:yes,no',
            'description' => 'nullable|string',
            'health_condition' => 'required|in:healthy,sick,injured',
            'adoption_fee' => 'nullable|numeric',
            'availability' => 'required|in:available,adopted',
            'images' => 'required|array|max:5',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048'
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
            $pet = $shelter->pets()->create($validatedData);

            $images = [];

            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/pets'), $imageName);
                $images[] = [
                    'path'=>'images/pets/' . $imageName,
                    'pet_id' => $pet->id
                ];
            }

            $pet->petImages()->createMany($images);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pet added successfully',
                'data' => $pet
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
