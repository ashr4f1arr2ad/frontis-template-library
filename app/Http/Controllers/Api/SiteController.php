<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Models\Cloud;
use App\Models\License;
use Illuminate\Support\Facades\Validator;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request)
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

        $license = null;
        $licenseValid = false;
    
        if ($request->filled('license_key')) {
            $license = License::where('license_key', $request->license_key)->first();
            $licenseValid = $license ? true : false;
        }

        $savedItems = [];

        // Step 2: If user is logged in, validate license & email
        if (!empty($request->input('email')) && $licenseValid) {
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
                $query = Cloud::where('user_id', $user->id)->where('item_type', 'sites');

                if ($sub_user->id === $user->id) {
                    $query->where('sub_user', $user->id);
                } else {
                    $query->where('sub_user', $sub_user->id); // Sub-user
                }

                $savedItems = $query->pluck('item_id')->map(fn($id) => (int)$id)->toArray();
            }
        }

        // Fetch categories with the count of associated patterns
        $categories = Category::withCount('sites')
            ->whereHas('sites')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'count' => $category->sites_count,
                ];
            });

        $tags = Site::select('tags')
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
        $query = Site::with('categories');

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
        $sites = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($site) use ($savedItems) {
                $colors = $site->colors;
                if (is_string($colors)) {
                    $colors = json_decode($colors, true);
                }

                $color_gradients = $site->color_gradients;
                if (is_string($color_gradients)) {
                    $color_gradients = json_decode($color_gradients, true);
                }
                
                $typographies = $site->typographies;
                if (is_string($typographies)) {
                    $typographies = json_decode($typographies, true);
                }

                $custom_typographies = $site->custom_typographies;
                if (is_string($custom_typographies)) {
                    $custom_typographies = json_decode($custom_typographies, true);
                }

                return [
                    'id' => $site->id,
                    'title' => $site->title,
                    'slug' => $site->slug,
                    'content' => $site->content,
                    'tags' => $site->tags,
                    'description' => $site->description,
                    'image' => $site->image,
                    'preview_url' => $site->preview_url,
                    'read_more_url' => $site->read_more_url,
                    'dependencies' => $site->dependencies,
                    'colors' => $colors,
                    'color_gradients' => $color_gradients,
                    'typographies' => $typographies,
                    'custom_typographies' => $custom_typographies,
                    'is_premium' => $site->is_premium,
                    'categories' => $site->categories->pluck('name')->toArray(),
                    'saved' => in_array((int)$site->id, $savedItems),
                ];
            });

        // Return the response in the desired JSON format
        return response()->json([
            'categories' => $categories,
            'tags' => $tags,
            'items' => $sites->items(),
            'pagination' => [
                'current_page' => $sites->currentPage(),
                'per_page' => $sites->perPage(),
                'total' => $sites->total(),
                'last_page' => $sites->lastPage(),
                'from' => $sites->firstItem(),
                'to' => $sites->lastItem(),
            ]
        ]);
    }

    public function get_site_by_id(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'site_slug' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $siteQuery = Site::query()
        ->select([
            'id',
            'content',
            'tags',
            'description',
            'image',
            'uploads_url',
            'read_more_url',
            'is_premium',
            'dependencies'
        ]);

        if ($request->site_id) {
            $siteQuery->where('id', $request->site_id);
        } else {
            $siteQuery->where('slug', $request->slug);
        }

        $site = $siteQuery->first();

        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site not found'
            ], 404);
        }    

        $categoryIds = \DB::table('category_site')
        ->where('site_id', $site->id)
        ->pluck('category_id');

        return response()->json([
            'success' => true,
            'data' => $site,
            'categories' => $categoryIds
        ]);
    }
}
