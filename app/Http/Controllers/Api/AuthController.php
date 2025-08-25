<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'device_name' => 'required|string',
            'secret_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check request input has secret key
        if($request->secret_key !== env('SECRET_KEY')) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to do this action',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

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
            'message' => 'User registered successfully',
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => 3600 // Access token expires in 1 hour (in seconds)
        ], 201);
    }

    /**
     * Log in a user and return an access token with refresh token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string',
            'secret_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check request input has secret key
        if($request->secret_key !== env('SECRET_KEY')) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to do this action',
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

    /**
     * Refresh the access token using the refresh token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Log out the user and revoke all tokens.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }
}
