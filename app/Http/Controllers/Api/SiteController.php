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

        // Fetch patterns with their associated categories
        $sites = Site::with('categories')
            ->get()
            ->map(function ($site) use ($savedItems) {
                $typographies = collect($site->typographies)->mapWithKeys(function ($item) {
                    return [
                        $item['name'] => [
                            'fontFamily' => $item['fontFamily'] ?? 'Default',
                            'fontWeight' => $item['fontWeight'] ?? 'Default',
                            'fontStyle' => $item['fontStyle'] ?? 'Default',
                            'textTransform' => $item['textTransform'] ?? 'Default',
                            'textDecoration' => $item['textDecoration'] ?? 'Default',
                            'fontSize' => $item['fontSize'] ?? [],
                            'fontSizeUnit' => $item['fontSizeUnit'] ?? [],
                            'lineHeight' => $item['lineHeight'] ?? [],
                            'lineHeightUnits' => $item['lineHeightUnits'] ?? [],
                            'letterSpacing' => $item['letterSpacing'] ?? [],
                            'letterSpacingUnit' => $item['letterSpacingUnit'] ?? [],
                        ]
                    ];
                });

                $custom_typographies = collect($site->custom_typographies)->mapWithKeys(function ($item) {
                    return [
                        $item['name'] => [
                            'fontFamily' => $item['fontFamily'] ?? 'Default',
                            'fontWeight' => $item['fontWeight'] ?? 'Default',
                            'fontStyle' => $item['fontStyle'] ?? 'Default',
                            'textTransform' => $item['textTransform'] ?? 'Default',
                            'textDecoration' => $item['textDecoration'] ?? 'Default',
                            'fontSize' => $item['fontSize'] ?? [],
                            'fontSizeUnit' => $item['fontSizeUnit'] ?? [],
                            'lineHeight' => $item['lineHeight'] ?? [],
                            'lineHeightUnits' => $item['lineHeightUnits'] ?? [],
                            'letterSpacing' => $item['letterSpacing'] ?? [],
                            'letterSpacingUnit' => $item['letterSpacingUnit'] ?? [],
                        ]
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
            'items' => $sites,
        ]);
    }
}
