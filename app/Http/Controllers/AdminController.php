<?php

namespace App\Http\Controllers;

use App\Models\Shelter;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    public function getUser(Request $request)
    {
        $user = JWTAuth::authenticate();
        if ($user->role !== 'admin') {
            return response()->json(['status' => "Failed", 'message' => 'Unauthorized'], 401);
        }
        $users = User::all()->where('role', 'user');
        $count = $users->count();
        return response()->json([
            'status' => "Success",
            'total_users'=> $count,
            'data' => $users], 200);
    }
    public function getShelter(Request $request)
    {
        $user = JWTAuth::authenticate();
        if ($user->role !== 'admin') {
            return response()->json(['status' => "Failed", 'message' => 'Unauthorized'], 401);
        }
        $shelters = Shelter::with('user','pets')->get();
        $count = $shelters->count();
        return response()->json([
            'status' => "Success",
            'total_users'=> $count,
            'data' => $shelters], 200);
    }
}
