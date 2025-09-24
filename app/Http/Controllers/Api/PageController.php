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

        $tags = Page::select('tags')
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
        $query = Page::with('categories');

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

        // Fetch patterns with their associated categories
        $pages = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($page) use ($savedItems) {
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
                    // 'page_json' => $page->page_json,
                    'categories' => $page->categories->pluck('name')->toArray(),
                    'saved' => in_array((int)$page->id, $savedItems),
                ];
            });

        // Return the response in the desired JSON format
        return response()->json([
            'categories' => $categories,
            'tags' => $tags,
            'items' => $pages->items(),
            'pagination' => [
                'current_page' => $pages->currentPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
                'last_page' => $pages->lastPage(),
                'from' => $pages->firstItem(),
                'to' => $pages->lastItem(),
            ]
        ]);
    }
}
