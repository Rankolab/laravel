<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LicenseController extends Controller
{
    /**
     * Validate a license key.
     * Note: The API doc implies domain is for context, not storage against the license directly.
     * This implementation checks if the key exists and is valid.
     */
    public function validateLicense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string|exists:users,license_key',
            'domain' => 'required|string|url', // Validate domain format
        ]);

        if ($validator->fails()) {
            // Provide a more specific error if the key doesn't exist
            if ($validator->errors()->has('license_key') && str_contains($validator->errors()->first('license_key'), 'exists')) {
                 return response()->json(['error' => 'Invalid license key'], 400);
            }
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = User::where('license_key', $request->license_key)->first();

        if (!$user) {
            // This case should technically be caught by 'exists' rule, but added for robustness
            return response()->json(['error' => 'Invalid license key'], 400);
        }

        // Check license validity (e.g., expiry date)
        $now = Carbon::now();
        $isExpired = $user->license_end_date && Carbon::parse($user->license_end_date)->lt($now);
        $isActive = $user->license_status === 'active' && !$isExpired;

        if (!$isActive) {
             // Determine specific reason for invalidity
             $errorReason = 'License is not active';
             if ($isExpired) {
                 $errorReason = 'License has expired';
             } elseif ($user->license_status === 'inactive') {
                 $errorReason = 'License is inactive';
             } // Add other status checks if needed
             
             // The API doc example shows 400 for invalid key, but maybe 403 Forbidden is better for inactive/expired?
             // Sticking to 400 as per example for now.
             return response()->json(['error' => $errorReason], 400); 
        }

        // If valid, return status
        return response()->json([
            'status' => $user->license_status,
            'license_type' => $user->license_type,
            'expires_at' => $user->license_end_date ? Carbon::parse($user->license_end_date)->toIso8601String() : null,
        ], 200);
    }

    /**
     * Get the license status for the authenticated user.
     */
    public function getStatus(Request $request)
    {
        $user = $request->user(); // Get authenticated user via Sanctum

        if (!$user->license_key) {
            return response()->json(['error' => 'License not found for this user'], 404);
        }

        // Check expiry status again for consistency
        $now = Carbon::now();
        $isExpired = $user->license_end_date && Carbon::parse($user->license_end_date)->lt($now);
        $currentStatus = $user->license_status;

        // Optionally update status to 'expired' if end date has passed and status is still 'active'
        if ($currentStatus === 'active' && $isExpired) {
            $user->license_status = 'expired';
            // Consider saving this change back to the database if desired
            // $user->save(); 
            $currentStatus = 'expired'; // Reflect the change in the response
        }

        return response()->json([
            'license_key' => $user->license_key,
            'status' => $currentStatus,
            'license_type' => $user->license_type,
            'expires_at' => $user->license_end_date ? Carbon::parse($user->license_end_date)->toIso8601String() : null,
        ], 200);
    }
}
