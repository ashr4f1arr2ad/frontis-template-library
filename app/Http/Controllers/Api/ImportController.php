<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Pattern;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function pattern(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'patterns_id' => 'required:exists:patterns,id',
        ]);

        // Get patterns by pattern id
        $pattern_json = Pattern::query()->where('id', $validated['patterns_id'])->value('patterns');

        return response()->json([
            'status' => true,
            'data' => $pattern_json,
        ], 200);
    }

    public function page(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'page_id' => 'required:exists:pages,id',
        ]);

        $page_json = Page::query()->where('id', $validated['page_id'])->value('pages');

    }
}
