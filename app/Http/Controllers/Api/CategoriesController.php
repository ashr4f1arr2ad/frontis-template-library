<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Category;

class CategoriesController extends Controller
{
    public function get_categories(Request $request): JsonResponse
    {
        $categories = Category::all();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }
}
