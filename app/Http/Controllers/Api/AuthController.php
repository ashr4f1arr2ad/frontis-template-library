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
use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *     title="Users API",
 *     description="API endpoints for managing users",
 *     version="1.0.0"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Enter token in format (Bearer <token>)"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "device_name"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="device_name", type="string", example="web"),
     *             @OA\Property(
     *                 property="subscription",
     *                 type="object",
     *                 nullable=true,
     *                 @OA\Property(property="title", type="string", example="Pro Plan"),
     *                 @OA\Property(property="type", type="string", example="monthly"),
     *                 @OA\Property(property="total_sites", type="integer", example=5),
     *                 @OA\Property(property="total_users", type="integer", example=2),
     *                 @OA\Property(property="license_key", type="string", example="abc123"),
     *                 @OA\Property(property="license_expiry", type="string", format="date", example="2025-12-31")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="access_token", type="string", example="1|abcdef"),
     *             @OA\Property(property="refresh_token", type="string", example="2|ghijk"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     * @OA\Schema(
     *     schema="User",
     *     type="object",
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="name", type="string"),
     *     @OA\Property(property="email", type="string"),
     *     @OA\Property(
     *         property="subscriptions",
     *         type="array",
     *         @OA\Items(
     *             type="object",
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="total_sites", type="integer"),
     *             @OA\Property(property="total_users", type="integer"),
     *             @OA\Property(property="license_key", type="string"),
     *             @OA\Property(property="license_expiry", type="string", format="date")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'device_name' => 'required|string',
            'subscription.title' => 'nullable|string|max:255',
            'subscription.type' => 'nullable|string',
            'subscription.total_sites' => 'nullable|integer|min:1',
            'subscription.total_users' => 'nullable|integer|min:0',
            'subscription.license_key' => 'nullable|string',
            'subscription.license_expiry' => 'nullable|date',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $accessToken = $user->createToken(
            $validated['device_name'],
            ['*'],
            now()->addHour()
        )->plainTextToken;

        $refreshToken = $user->createToken(
            $validated['device_name'] . '-refresh',
            ['refresh'],
            now()->addDays(7)
        )->plainTextToken;

        if (!empty($validated['subscription']['title'])) {
            $subscription = Subscription::create([
                'title' => $validated['subscription']['title'],
                'type' => $validated['subscription']['type'] ?? null,
                'total_sites' => $validated['subscription']['total_sites'] ?? null,
                'total_users' => $validated['subscription']['total_users'] ?? 0,
                'license_key' => $validated['subscription']['license_key'] ?? null,
                'license_expiry' => $validated['subscription']['license_expiry'] ?? null,
            ]);

            $user->subscriptions()->attach($subscription->id);
        }

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'user' => $user->load('subscriptions'),
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => 3600
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Log in a user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "device_name"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="device_name", type="string", example="web")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="access_token", type="string", example="1|abcdef"),
     *             @OA\Property(property="refresh_token", type="string", example="2|ghijk"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string',
            'hashed_password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!Auth::attempt($request->only('email', 'password'))) {
            if ($user->password !== $request->hashed_password) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
        }

        if ($user->password !== $request->password) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $accessToken = $user->createToken(
            $request->device_name,
            ['*'],
            now()->addHour()
        )->plainTextToken;

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
            'expires_in' => 3600
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Refresh user token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token", "device_name"},
     *             @OA\Property(property="refresh_token", type="string", example="2|ghijk"),
     *             @OA\Property(property="device_name", type="string", example="web")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully"),
     *             @OA\Property(property="access_token", type="string", example="3|newtoken"),
     *             @OA\Property(property="refresh_token", type="string", example="4|newrefresh"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid or expired refresh token"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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

        $refreshToken = PersonalAccessToken::findToken($request->refresh_token);

        if (!$refreshToken || !$refreshToken->tokenable || !in_array('refresh', $refreshToken->abilities)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired refresh token'
            ], 401);
        }

        $refreshToken->delete();

        $user = $refreshToken->tokenable;

        $newAccessToken = $user->createToken(
            $request->device_name,
            ['*'],
            now()->addHour()
        )->plainTextToken;

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
            'expires_in' => 3600
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Log out a user",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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
