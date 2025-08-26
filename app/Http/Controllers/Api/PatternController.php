<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatternResource;
use App\Models\Pattern;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PatternController extends Controller
{
    public function index()
    {
        // Fetch categories with the count of associated patterns
        $categories = Category::withCount('patterns')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'count' => $category->patterns_count,
                ];
            });

        // Fetch patterns with their associated categories
        $patterns = Pattern::with('categories')
            ->get()
            ->map(function ($pattern) {
                return [
                    'id' => $pattern->id,
                    'title' => $pattern->title,
                    'slug' => $pattern->slug,
                    'description' => $pattern->description,
                    'is_premium' => $pattern->is_premium,
                    'image' => $pattern->image,
                    'tags' => $pattern->tags,
                    'categories' => $pattern->categories->pluck('name')->toArray(),
                ];
            });

        // Return the response in the desired JSON format
        return response()->json([
            'categories' => $categories,
            'items' => $patterns,
        ]);
    }
}
