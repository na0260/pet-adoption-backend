<?php

namespace App\Http\Controllers;

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
        $count = count($users);
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
        $users = User::all()->where('role', 'shelter');
        $count = count($users);
        return response()->json([
            'status' => "Success",
            'total_users'=> $count,
            'data' => $users], 200);
    }
}
