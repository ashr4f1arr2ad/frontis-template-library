<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatternResource;
use App\Models\User;
use App\Models\Cloud;
use App\Models\License;
use App\Models\Pattern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PatternController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Step 1: Validate "logged_in"
        $validator = Validator::make($request->all(), [
            'logged_in' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $savedItems = [];

        // Step 2: If user is logged in, validate license & email
        if ($request->input('logged_in') === 'true') {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'license_key' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $license = License::where('license_key', $request->license_key)->first();

            if (!$license) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'License not found',
                ], 404);
            }

            $user = $license->user;
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No user associated with this license',
                ], 404);
            }

            $sub_user = User::where('email', $request->email)->first();

            if ($sub_user) {
                $query = Cloud::where('user_id', $user->id);

                if ($sub_user->id === $user->id) {
                    $query->whereNull('sub_user'); // Main user
                } else {
                    $query->where('sub_user', $sub_user->id); // Sub-user
                }

                $savedItems = $query->pluck('item_id')->map(fn($id) => (int)$id)->toArray();
            }
        }

        // Step 3: Fetch categories with count (common for both cases)
        $categories = Category::withCount('patterns')
            ->whereHas('patterns')
            ->get()
            ->map(fn($category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'count' => $category->patterns_count,
            ]);

        // Step 4: Fetch patterns with categories (common, just attach saved flag if logged in)
        $patterns = Pattern::with('categories')->get()
            ->map(function ($pattern) use ($savedItems) {
                return [
                    'id' => $pattern->id,
                    'title' => $pattern->title,
                    'slug' => $pattern->slug,
                    'description' => $pattern->description,
                    'is_premium' => $pattern->is_premium,
                    'image' => $pattern->image,
                    'tags' => $pattern->tags,
                    'categories' => $pattern->categories->pluck('name')->toArray(),
                    'saved' => in_array((int)$pattern->id, $savedItems),
                ];
            });

        // Step 5: Return response
        return response()->json([
            'categories' => $categories,
            'items' => $patterns
        ])->withHeaders([
            'Access-Control-Allow-Origin' => 'http://frontis.local',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ]);
    }
}
