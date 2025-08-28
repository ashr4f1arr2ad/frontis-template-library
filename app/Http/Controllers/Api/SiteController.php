<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request)
    {
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
            ->map(function ($site) {
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
                    'description' => $site->description,
                    'is_premium' => $site->is_premium,
                    'image' => $site->image,
                    'tags' => $site->tags,
                    'dependencies' => $site->dependencies,
                    'typographies' => $typographies,
                    'custom_typographies' => $custom_typographies,
                    'colors' => $site->colors,
                    'color_gradients' => $site->color_gradients,
                    'categories' => $site->categories->pluck('name')->toArray(),
                ];
            });

        // Return the response in the desired JSON format
        return response()->json([
            'categories' => $categories,
            'items' => $sites,
        ]);
    }
}
