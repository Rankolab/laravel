<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Assuming User model exists or will be created
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'password_hash' => Hash::make($request->password), // Use password_hash to match migration
            // Set default license status if needed, based on migration defaults
            'license_status' => 'inactive', 
        ]);

        // Exclude password_hash from the response
        $userResponse = $user->only(['user_id', 'email', 'username']);

        return response()->json($userResponse, 201);
    }

    /**
     * Authenticate a user and return a token.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
             return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Generate Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
