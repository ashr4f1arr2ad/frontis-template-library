<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Models\Cloud;
use App\Models\License;
use Illuminate\Support\Facades\Validator;
use App\Models\Page;
use App\Models\Pattern;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public  function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email',
            'license_key' => 'nullable|string|max:255'
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
        if (!empty($request->input('email'))) {
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
                $query = Cloud::where('user_id', $user->id)->where('item_type', 'pages');

                if ($sub_user->id === $user->id) {
                    $query->whereNull('sub_user'); // Main user
                } else {
                    $query->where('sub_user', $sub_user->id); // Sub-user
                }

                $savedItems = $query->pluck('item_id')->map(fn($id) => (int)$id)->toArray();
            }
        }

        // Fetch categories with the count of associated patterns
        $categories = Category::withCount('pages')
            ->whereHas('pages')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'count' => $category->pages_count,
                ];
            });

        // Fetch patterns with their associated categories
        $pages = Page::with('categories')
            ->get()
            ->map(function ($page) use ($savedItems) {
                return [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'content' => $page->content,
                    'tags' => $page->tags,
                    'description' => $page->description,
                    'image' => $page->image,
                    'dependencies' => $page->dependencies,
                    'is_premium' => $page->is_premium,
                    'page_json' => $page->page_json,
                    'categories' => $page->categories->pluck('name')->toArray(),
                    'saved' => in_array((int)$page->id, $savedItems),
                ];
            });

        // Return the response in the desired JSON format
        return response()->json([
            'categories' => $categories,
            'items' => $pages,
        ]);
    }
}
