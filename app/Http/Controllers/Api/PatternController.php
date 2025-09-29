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
            'email' => 'nullable|email',
            'license_key' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'tag' => 'nullable|string',
            'search' => 'nullable|string',
            'category' => 'nullable|string',
            'is_pro_template' => 'nullable|string',
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
        if (!empty($request->input('email')) && !empty($request->input('license_key'))) {
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
                $query = Cloud::where('user_id', $user->id)->where('item_type', 'patterns');

                if ($sub_user->id === $user->id) {
                    $query->where('sub_user', $user->id);
                } else {
                    $query->where('sub_user', $sub_user->id);
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

        $tags = Pattern::select('tags')
            ->whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->map(fn($tag) => trim($tag))
            ->filter()
            ->countBy()
            ->map(fn($count, $tag) => [
                'name' => $tag,
                'count' => $count,
            ])
            ->values();

        // Pagination setup
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 12);

        // Step 3: Build patterns query
        $query = Pattern::with('categories');

        // Step 4: Tag filter (single or multiple)
        if ($request->filled('tag')) {
            $tags = (array) $request->input('tag');
            foreach ($tags as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($request->filled('category')) {
            $filterCategories = (array) $request->input('category');
    
            $query->whereHas('categories', function ($q) use ($filterCategories) {
                $slugs = array_filter($filterCategories, fn($v) => !is_numeric($v));
                $ids   = array_filter($filterCategories, fn($v) => is_numeric($v));
    
                if ($slugs) {
                    $q->whereIn('categories.slug', $slugs);
                }
    
                if ($ids) {
                    $q->orWhereIn('categories.id', $ids);
                }
            });
        }

        if ($request->filled('is_pro_template')) {
            if ($request->is_pro_template === 'pro') {
                $query->where('is_premium', '1');
            } elseif ($request->is_pro_template === 'free') {
                $query->where('is_premium', '0');
            }
        }

        // Step 5: Paginate and transform
        $patterns = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($pattern) use ($savedItems) {
                return [
                    'id' => $pattern->id,
                    'title' => $pattern->title,
                    'slug' => $pattern->slug,
                    'description' => $pattern->description,
                    'is_premium' => $pattern->is_premium,
                    'image' => $pattern->image,
                    'tags' => $pattern->tags,
                    // 'pattern_json' => $pattern->pattern_json,
                    'categories' => $pattern->categories->pluck('name')->toArray(),
                    'saved' => in_array((int)$pattern->id, $savedItems),
                ];
            });

        // Step 5: Return response
        return response()->json([
            'categories' => $categories,
            'tags' => $tags,
            'items' => $patterns->items(),
            'pagination' => [
                'current_page' => $patterns->currentPage(),
                'per_page' => $patterns->perPage(),
                'total' => $patterns->total(),
                'last_page' => $patterns->lastPage(),
                'from' => $patterns->firstItem(),
                'to' => $patterns->lastItem(),
            ]
        ]);
    }
}
