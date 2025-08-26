<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'device_name' => 'required|string', // Added device_name validation
            'subscription.title' => 'nullable|string|max:255',
            'subscription.type' => 'nullable|string',
            'subscription.total_sites' => 'nullable|integer|min:1',
            'subscription.total_users' => 'nullable|integer|min:0',
            'subscription.license_key' => 'nullable|string',
            'subscription.license_expiry' => 'nullable|date',
        ]);

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Create access token with expiration (e.g., 1 hour)
        $accessToken = $user->createToken(
            $validated['device_name'],
            ['*'],
            now()->addHour()
        )->plainTextToken;

        // Create refresh token with longer expiration (e.g., 7 days)
        $refreshToken = $user->createToken(
            $validated['device_name'] . '-refresh',
            ['refresh'],
            now()->addDays(7)
        )->plainTextToken;

        // Check if subscription data is provided
        if (!empty($validated['subscription']['title'])) {
            // Create the subscription
            $subscription = Subscription::create([
                'title' => $validated['subscription']['title'],
                'type' => $validated['subscription']['type'] ?? null,
                'total_sites' => $validated['subscription']['total_sites'] ?? null,
                'total_users' => $validated['subscription']['total_users'] ?? 0,
                'license_key' => $validated['subscription']['license_key'] ?? null,
                'license_expiry' => $validated['subscription']['license_expiry'] ?? null,
            ]);

            // Attach the subscription to the user
            $user->subscriptions()->attach($subscription->id);
        }

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'user' => $user->load('subscriptions'),
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => 3600 // Access token expires in 1 hour (in seconds)
        ], 201);
    }

    // Other methods (login, refresh, logout) remain unchanged
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();

        // Create access token with expiration (e.g., 1 hour)
        $accessToken = $user->createToken(
            $request->device_name,
            ['*'],
            now()->addHour()
        )->plainTextToken;

        // Create refresh token with longer expiration (e.g., 7 days)
        $refreshToken = $user->createToken(
            $request->device_name . '-refresh',
            ['refresh'],
            now()->addDays(7)
        )->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => 3600 // Access token expires in 1 hour (in seconds)
        ], 200);
    }

    public function refresh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
            'device_name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the refresh token
        $refreshToken = PersonalAccessToken::findToken($request->refresh_token);

        if (!$refreshToken || !$refreshToken->tokenable || !in_array('refresh', $refreshToken->abilities)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired refresh token'
            ], 401);
        }

        // Revoke the old refresh token
        $refreshToken->delete();

        $user = $refreshToken->tokenable;

        // Create new access token
        $newAccessToken = $user->createToken(
            $request->device_name,
            ['*'],
            now()->addHour()
        )->plainTextToken;

        // Create new refresh token
        $newRefreshToken = $user->createToken(
            $request->device_name . '-refresh',
            ['refresh'],
            now()->addDays(7)
        )->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Token refreshed successfully',
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'expires_in' => 3600 // New access token expires in 1 hour
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }
}
