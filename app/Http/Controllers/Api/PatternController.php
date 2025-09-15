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

        // Find the license by license_key
        $license = License::where('license_key', $request->input('license_key'))->first();
        if (!$license) {
            return response()->json([
                'status' => 'error',
                'message' => 'License not found',
            ], 404);
        }

        // Get the user associated with the license
        $user = $license->user;
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user associated with this license',
            ], 404);
        }

        $sub_user = User::where('email', $request->input('email'))->first();

        // Determine saved items based on user type
        if ($sub_user->id === $user->id) {
            // Main user: Fetch items where user_id matches and sub_user is null
            $savedItems = Cloud::where('user_id', $user->id)
                ->whereNull('sub_user')
                ->pluck('item_id')
                ->map(fn($id) => (int)$id)
                ->toArray();
        } else {
            // Sub-user: Fetch items where user_id matches main user and sub_user matches sub_user
            $savedItems = Cloud::where('user_id', $user->id)
                ->where('sub_user', $sub_user->id)
                ->pluck('item_id')
                ->map(fn($id) => (int)$id)
                ->toArray();
        }

        // Fetch categories with the count of associated patterns
        $categories = Category::withCount('patterns')
            ->whereHas('patterns')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'count' => $category->patterns_count,
                ];
            });

        // Fetch patterns with their associated categories
        $patterns = Pattern::with('categories')
            ->get()
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

        // Return the response in the desired JSON format
        return response()->json([
            'categories' => $categories,
            'items' => $patterns
        ])->header('Access-Control-Allow-Origin', 'http://frontis.local')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
