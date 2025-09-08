<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Pattern;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public  function index(Request $request): \Illuminate\Http\JsonResponse
    {
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
            ->map(function ($page) {
                return [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'description' => $page->description,
                    'is_premium' => $page->is_premium,
                    'image' => $page->image,
                    'tags' => $page->tags,
                    'dependencies' => $page->dependencies,
                    'categories' => $page->categories->pluck('name')->toArray(),
                ];
            });

        // Return the response in the desired JSON format
        return response()->json([
            'categories' => $categories,
            'items' => $pages,
        ]);
    }
}
