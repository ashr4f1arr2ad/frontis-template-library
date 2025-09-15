<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cloud;
use App\Models\License;
use App\Models\Pattern;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class CloudController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user associated with this email',
            ], 404);
        }

        // Fetch categories with the count of associated patterns
        $categories = Category::withCount('patterns')
        ->whereHas('patterns')
        ->get()
        ->map(function ($category) {
            return [
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'count' => $category->patterns_count,
            ];
        });

        // Fetch clouds and attach patterns by item_id
        $clouds = Cloud::where('sub_user', $user->id)->get()->map(function ($cloud) {
            $pattern = Pattern::with('categories')->find($cloud->item_id);
            $created = Carbon::parse($cloud->created_at);
    
            return [
                'cloud_id' => $cloud->id,
                'item_type' => $cloud->item_type,
                'item_id'  => $cloud->item_id,
                'id'          => $pattern->id,
                'title'       => $pattern->title,
                'slug'        => $pattern->slug,
                'is_premium'  => $pattern->is_premium,
                'image'       => $pattern->image,
                'dateAndTime' => [
                    'day'   => $created->format('j'),
                    'month' => $created->format('F'),
                    'year'  => $created->format('Y'),
                    'time'  => $created->format('h:i A'),
                ],
            ];
        });

        // Return the response in the desired JSON format
        return response()->json([
            'categories' => $categories,
            'items' => $clouds
        ])->header('Access-Control-Allow-Origin', 'http://frontis.local')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    /**
     * Store a new item in the Cloud using a license key.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saved_item(Request $request): JsonResponse
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'item_id' => 'required|integer',
            'item_type' => 'required|string|max:255',
            'website' => 'required|string|max:255',
            'license_key' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find the license by license_key
        $license = License::where('license_key', $request->input('license_key'))->first();
        if (!$license) {
            return response()->json([
                'status' => 'error',
                'message' => 'License not found',
            ], 404);
        }

        // Get the user associated with the license
        $user = $license->user;
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user associated with this license',
            ], 404);
        }

        // Check if the user's email is verified
//        if (is_null($user->email_verified_at)) {
//            return response()->json([
//                'status' => 'error',
//                'message' => 'User email not verified',
//            ], 403);
//        }

        $sub_user = User::where('email', $request->input('email'))->first();
        if (!$sub_user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user associated with this email',
            ], 404);
        }

        $data = $request->input('data');
        if (is_null($data)) {
            $pattern_json = Pattern::where('id', $request->input('item_id'))->value('pattern_json');

            if (is_null($pattern_json)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pattern not found for given item_id',
                ], 404);
            }

            $data = $pattern_json;
        }

        // Create a new Cloud record
        $cloud = Cloud::create([
            'sub_user' => $sub_user->id,
            'user_id' => $user->id,
            'item_id' => $request->input('item_id'),
            'item_type' => $request->input('item_type'),
            'data' => $data,
            'website' => $request->input('website'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Item saved successfully',
            'data' => $cloud,
        ], 201);
    }
}
