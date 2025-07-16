<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:60|unique:users',
            'password' => 'required|string|min:8|max:30|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken
        ]);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if( ! $user || ! Hash::check($request->password, $user->password)){
            return response()->json(['message' => 'Login Gagal'], 401);
    }

        return response()->json([
            'user'=>$user,
            'token'=>$user->createToken('api-token')->plainTextToken
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged Out']);
    }
}
