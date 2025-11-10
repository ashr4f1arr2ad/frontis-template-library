<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\User;
use App\Models\Cloud;
use App\Models\License;
use App\Models\Pattern;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ResourcesController extends Controller
{
    public function resources(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'category' => 'required|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $categorySlug = $request->input('category');
        $type = $request->input('type');

        if ($type === 'patterns') {
            $category = Category::where('slug', $categorySlug)->first();
        
            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found for slug: ' . $categorySlug,
                ], 404);
            }
        
            $patternIds = DB::table('category_pattern')
                ->where('category_id', $category->id)
                ->pluck('pattern_id')
                ->toArray();
        
            if (count($patternIds) === 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No patterns found for this category',
                    'items' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => (int) $request->input('per_page', 12),
                        'total' => 0,
                        'last_page' => 0,
                        'from' => null,
                        'to' => null,
                    ],
                ]);
            }
        
            $page = (int) $request->input('page', 1);
            $perPage = (int) $request->input('per_page', 12);
        
            $query = Pattern::select('id', 'title', 'slug', 'image', 'preview_url', 'read_more_url', 'is_premium')->whereIn('id', $patternIds);
        
            $patterns = $query->paginate($perPage, ['*'], 'page', $page)
                ->through(function ($pattern) {
                    return [
                        'id' => $pattern->id,
                        'title' => $pattern->title,
                        'slug' => $pattern->slug,
                        'image' => $pattern->image,
                        'preview_url' => $pattern->preview_url,
                        'read_more_url' => $pattern->read_more_url,
                        'is_premium' => $pattern->is_premium,
                    ];
                });
        
            return response()->json([
                'status' => 'success',
                'items' => $patterns->items(),
                'pagination' => [
                    'current_page' => $patterns->currentPage(),
                    'per_page' => $patterns->perPage(),
                    'total' => $patterns->total(),
                    'last_page' => $patterns->lastPage(),
                    'from' => $patterns->firstItem(),
                    'to' => $patterns->lastItem(),
                ],
            ]);
        }

        if ($type === 'pages') {
            $category = Category::where('slug', $categorySlug)->first();
        
            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found for slug: ' . $categorySlug,
                ], 404);
            }
        
            $pagesIds = DB::table('category_page')
                ->where('category_id', $category->id)
                ->pluck('page_id')
                ->toArray();
        
            if (count($siteIds) === 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No pages found for this category',
                    'items' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => (int) $request->input('per_page', 12),
                        'total' => 0,
                        'last_page' => 0,
                        'from' => null,
                        'to' => null,
                    ],
                ]);
            }
        
            $page = (int) $request->input('page', 1);
            $perPage = (int) $request->input('per_page', 12);
        
            $query = Page::select('id', 'title', 'slug', 'image', 'preview_url', 'read_more_url', 'is_premium')->whereIn('id', $pagesIds);
        
            $pages = $query->paginate($perPage, ['*'], 'page', $page)
                ->through(function ($site) {
                    return [
                        'id' => $page->id,
                        'title' => $page->title,
                        'slug' => $page->slug,
                        'image' => $page->image,
                        'preview_url' => $page->preview_url,
                        'read_more_url' => $page->read_more_url,
                        'is_premium' => $page->is_premium,
                    ];
                });
        
            return response()->json([
                'status' => 'success',
                'items' => $pages->items(),
                'pagination' => [
                    'current_page' => $pages->currentPage(),
                    'per_page' => $pages->perPage(),
                    'total' => $pages->total(),
                    'last_page' => $pages->lastPage(),
                    'from' => $pages->firstItem(),
                    'to' => $pages->lastItem(),
                ],
            ]);
        }

        if ($type === 'sites') {
            $category = Category::where('slug', $categorySlug)->first();
        
            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found for slug: ' . $categorySlug,
                ], 404);
            }
        
            $siteIds = DB::table('category_site')
                ->where('category_id', $category->id)
                ->pluck('site_id')
                ->toArray();
        
            if (count($siteIds) === 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No sites found for this category',
                    'items' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => (int) $request->input('per_page', 12),
                        'total' => 0,
                        'last_page' => 0,
                        'from' => null,
                        'to' => null,
                    ],
                ]);
            }
        
            $page = (int) $request->input('page', 1);
            $perPage = (int) $request->input('per_page', 12);
        
            $query = Site::select('id', 'title', 'slug', 'image', 'preview_url', 'read_more_url', 'is_premium')->whereIn('id', $siteIds);
        
            $sites = $query->paginate($perPage, ['*'], 'page', $page)
                ->through(function ($site) {
                    return [
                        'id' => $site->id,
                        'title' => $site->title,
                        'slug' => $site->slug,
                        'image' => $site->image,
                        'preview_url' => $site->preview_url,
                        'read_more_url' => $site->read_more_url,
                        'is_premium' => $site->is_premium,
                    ];
                });
        
            return response()->json([
                'status' => 'success',
                'items' => $sites->items(),
                'pagination' => [
                    'current_page' => $sites->currentPage(),
                    'per_page' => $sites->perPage(),
                    'total' => $sites->total(),
                    'last_page' => $sites->lastPage(),
                    'from' => $sites->firstItem(),
                    'to' => $sites->lastItem(),
                ],
            ]);
        }
    }
}
