<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class PetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pets = Pet::with('shelter','petImages')->get();
        $count = $pets->count();
        return response()->json([
            'status' => 'success',
            'total_pets' => $count,
            'data' => $pets,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = JWTAuth::authenticate();

        if ($user->role === 'user') {
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pet = Pet::with('shelter','petImages')->find($id);

        if (!$pet) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pet not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pet
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
