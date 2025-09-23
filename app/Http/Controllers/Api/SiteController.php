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
                $query = Cloud::where('user_id', $user->id)->where('item_type', 'sites');

                if ($sub_user->id === $user->id) {
                    $query->whereNull('sub_user'); // Main user
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
                $typographies = collect($site->typographies)
                    ->reduce(function ($carry, $item) {
                        $name = strtolower($item['name']);

                        $data = [
                            'fontFamily'       => $item['fontFamily'] ?? 'Default',
                            'fontWeight'       => $item['fontWeight'] ?? 'Default',
                            'fontStyle'        => $item['fontStyle'] ?? 'Default',
                            'textTransform'    => $item['textTransform'] ?? 'Default',
                            'textDecoration'   => $item['textDecoration'] ?? 'Default',
                            'fontSize'         => $item['fontSize'] ?? [],
                            'fontSizeUnit'     => $item['fontSizeUnit'] ?? [],
                            'lineHeight'       => $item['lineHeight'] ?? [],
                            'lineHeightUnits'  => $item['lineHeightUnits'] ?? [],
                            'letterSpacing'    => $item['letterSpacing'] ?? [],
                            'letterSpacingUnit'=> $item['letterSpacingUnit'] ?? [],
                        ];

                        // convert nulls in nested arrays to empty strings
                        foreach (['fontSize','lineHeight','letterSpacing'] as $key) {
                            if (isset($data[$key]) && is_array($data[$key])) {
                                foreach ($data[$key] as $device => $value) {
                                    if (is_null($value)) {
                                        $data[$key][$device] = '';
                                    }
                                }
                            }
                        }

                        // handle heading vs top-level
                        if (in_array($name, ['all', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                            if (!isset($carry['heading'])) $carry['heading'] = [];
                            $carry['heading'][$name] = isset($carry['heading'][$name])
                                ? array_merge_recursive($carry['heading'][$name], $data)
                                : $data;
                        } else {
                            $carry[$name] = isset($carry[$name])
                                ? array_merge_recursive($carry[$name], $data)
                                : $data;
                        }

                        return $carry;
                    }, []);

                $custom_typographies = collect($site->custom_typographies)
                    ->mapWithKeys(function ($item) {
                        $data = [
                            'fontFamily'       => $item['fontFamily'] ?? 'Default',
                            'fontWeight'       => $item['fontWeight'] ?? 'Default',
                            'fontStyle'        => $item['fontStyle'] ?? 'Default',
                            'textTransform'    => $item['textTransform'] ?? 'Default',
                            'textDecoration'   => $item['textDecoration'] ?? 'Default',
                            'fontSize'         => $item['fontSize'] ?? [],
                            'fontSizeUnit'     => $item['fontSizeUnit'] ?? [],
                            'lineHeight'       => $item['lineHeight'] ?? [],
                            'lineHeightUnits'  => $item['lineHeightUnits'] ?? [],
                            'letterSpacing'    => $item['letterSpacing'] ?? [],
                            'letterSpacingUnit'=> $item['letterSpacingUnit'] ?? [],
                        ];

                        // Only convert null values to empty strings for fontSize, lineHeight, letterSpacing
                        foreach (['fontSize', 'lineHeight', 'letterSpacing'] as $key) {
                            if (isset($data[$key]) && is_array($data[$key])) {
                                foreach ($data[$key] as $device => $value) {
                                    if (is_null($value)) {
                                        $data[$key][$device] = '';
                                    }
                                }
                            }
                        }

                        return [
                            $item['name'] => $data
                        ];
                    });

                return [
                    'id' => $site->id,
                    'title' => $site->title,
                    'slug' => $site->slug,
                    'content' => $site->content,
                    'tags' => $site->tags,
                    'description' => $site->description,
                    'image' => $site->image,
                    'dependencies' => $site->dependencies,
                    'colors' => $site->colors,
                    'color_gradients' => $site->color_gradients,
                    'typographies' => $typographies,
                    'custom_typographies' => $custom_typographies,
                    'pages' => $site->pages,
                    'is_premium' => $site->is_premium,
                    'categories' => $site->categories->pluck('name')->toArray(),
                    'saved' => in_array((int)$site->id, $savedItems),
                ];
            });

        // Return the response in the desired JSON format
        return response()->json([
            'categories' => $categories,
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
}
