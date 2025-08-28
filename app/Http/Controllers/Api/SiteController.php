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
                return [
                    'id' => $site->id,
                    'title' => $site->title,
                    'slug' => $site->slug,
                    'description' => $site->description,
                    'is_premium' => $site->is_premium,
                    'image' => $site->image,
                    'tags' => $site->tags,
                    'dependencies' => $site->dependencies,
                    'typographies' => $site->typographies,
                    'colors' => $site->colors,
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
