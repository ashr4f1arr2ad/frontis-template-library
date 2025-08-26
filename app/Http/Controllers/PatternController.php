<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pattern;

class PatternController extends Controller
{
    public function index(Request $request) {
        $patterns = Pattern::select('id', 'title', 'slug', 'description', 'is_premium', 'image', 'tags')->get()
            ->map(function ($pattern) {
                $pattern->image = '/storage/' . $pattern->image; // Add full path to image
                return $pattern;
            });

        return response()->json($patterns);
    }
}
