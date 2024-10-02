<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdoptionApplicationController extends Controller
{
    public function submitApplication(Request $request, string $id): JsonResponse
    {
        $user = JWTAuth::authenticate();
        if ($user->role !== 'user') {
            return response()->json(['message' => 'Only users can submit adoption applications.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'address' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'phone' => 'required|string|digits:11',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();

        if ($user->adoptionApplications()->where('pet_id', $id)->exists()) {
            return response()->json(['message' => 'You have already submitted an adoption application for this pet.'], 400);
        }

        $pet = Pet::find($id);
        if (!$pet){
            return response()->json(['message' => 'Pet not found.'], 404);
        }
        if ($pet->availability !== 'available') {
            return response()->json(['message' => 'This pet is not available for adoption.'], 400);
        }
        $application = $user->adoptionApplications()->create([
            'pet_id' => $id,
            'address' => $validatedData['address'],
            'city' => $validatedData['city'],
            'postal_code' => $validatedData['postal_code'],
            'phone' => $validatedData['phone'],
            'reason' => $validatedData['reason'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Adoption application submitted successfully.',
            'data' => $application->load('pet'),
        ], 201);
    }
}
