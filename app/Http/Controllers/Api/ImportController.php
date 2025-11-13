<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Pattern;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function pattern(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'pattern_id' => 'required|exists:patterns,id',
        ]);

        // Get pattern JSON by pattern ID
        $pattern_json = Pattern::query()->where('id', $validated['pattern_id'])->value('pattern_json');

        // Check if pattern_json is null (record not found)
        if (is_null($pattern_json)) {
            return response()->json([
                'status' => false,
                'message' => 'Pattern not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $pattern_json,
        ], 200)->header('Access-Control-Allow-Origin', 'http://frontis.local')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    public function page(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'page_id' => 'required|exists:pages,id',
        ]);

        // Get page JSON by page ID
        $page_json = Page::query()->where('id', $validated['page_id'])->value('page_json');

        // Check if page_json is null (record not found)
        if (is_null($page_json)) {
            return response()->json([
                'status' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $page_json,
        ], 200);
    }

    public function site(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
        ]);

        // Get pages JSON by site ID
        // $site_json = Site::query()->where('id', $validated['site_id'])->value('pages');
        $site_json = Site::query()->select(['pages', 'uploads_url', 'templates', 'posts', 'headers', 'footers'])->where('id', $validated['site_id'])->first();

        // Check if site_json is null (record not found)
        if (is_null($site_json)) {
            return response()->json([
                'status' => false,
                'message' => 'Site not found.',
            ], 404);
        }

        $pages = [];
        foreach($site_json["pages"] as $page) {
            $page_content = json_decode($page["page"]);
            $pages[] = [
                "title" => $page["name"],
                "slug"  => Str::slug($page["name"]),
                "content" => $page_content->content,
                "template" => $page["template"]
            ];
        }

        $data = [
            "pages" => $pages,
            "templates" => json_decode($site_json["templates"]),
            "headers" => json_decode($site_json["headers"]),
            "footers" => json_decode($site_json["footers"]),
            "posts" => json_decode($site_json["posts"]),
            "uploads_url" => $site_json["uploads_url"]
        ];

        return response()->json([
            'status' => true,
            'data' => $data
        ], 200);
    }
}
