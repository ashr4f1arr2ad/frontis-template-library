<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatternResource;
use App\Models\Pattern;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PatternController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return PatternResource::collection(Pattern::with('tags')->get());
    }

    public function show($id): PatternResource
    {
        return new PatternResource(Pattern::with('tags')->findOrFail($id));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:patterns',
            'description' => 'nullable|string',
            'is_premium' => 'boolean',
            'image' => 'nullable|image|max:1024', // 1MB limit
            'patterns' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('patterns', 'public');
        }

        $pattern = Pattern::create($validated);
        if (isset($validated['tags'])) {
            $tagIds = [];
            foreach ($validated['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(
                    ['name' => $tagName],
                    ['slug' => Str::slug($tagName)]
                );
                $tagIds[] = $tag->id;
            }
            $pattern->tags()->sync($tagIds);
        }

        return new PatternResource($pattern->load('tags'));
    }

    public function update(Request $request, $id) {
        $pattern = Pattern::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:patterns,slug,' . $id,
            'description' => 'nullable|string',
            'is_premium' => 'boolean',
            'image' => 'nullable|image|max:1024',
            'patterns' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('patterns', 'public');
        }

        $pattern->update($validated);
        if (isset($validated['tags'])) {
            $tagIds = [];
            foreach ($validated['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(
                    ['name' => $tagName],
                    ['slug' => Str::slug($tagName)]
                );
                $tagIds[] = $tag->id;
            }
            $pattern->tags()->sync($tagIds);
        }

        return new PatternResource($pattern->load('tags'));
    }

    public function destroy($id) {
        $pattern = Pattern::findOrFail($id);
        $pattern->delete();
        return response()->json(['message' => 'Pattern deleted']);
    }
}
