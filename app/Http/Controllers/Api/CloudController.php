<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cloud;
use App\Models\License;
use App\Models\Pattern;
use App\Models\Page;
use App\Models\Site;
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
            'email' => 'required|email',
            'license_key' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $license = License::where('license_key', $request->license_key)->first();

        if (!$license) {
            return response()->json([
                'status' => 'error',
                'message' => 'License not found',
            ], 404);
        }

        $user = $license->user;
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user associated with this license',
            ], 404);
        }

        $sub_user = User::where('email', $request->email)->first();

        $query = Cloud::where('user_id', $user->id);

        if ($sub_user->id === $user->id) {
            $query->whereNull('sub_user'); // Main user
        } else {
            $query->where(function ($q) use ($sub_user) {
                $q->whereNull('sub_user') // main user items
                  ->orWhere('sub_user', $sub_user->id); // sub-user items
            });
        }

        // Fetch all clouds (patterns, sites, pages)
        $clouds = $query->get()->map(function ($cloud) {
            $created = Carbon::parse($cloud->created_at);

            switch ($cloud->item_type) {
                case 'patterns':
                    $item = Pattern::find($cloud->item_id);
                    if (!$item) return null;
                    return [
                        'cloud_id'    => $cloud->id,
                        'item_type'   => $cloud->item_type,
                        'item_id'     => $cloud->item_id,
                        'id'          => $item->id,
                        'title'       => $item->title,
                        'slug'        => $item->slug,
                        'is_premium'  => $item->is_premium,
                        'image'       => $item->image,
                        'dateAndTime' => [
                            'day'   => $created->format('j'),
                            'month' => $created->format('F'),
                            'year'  => $created->format('Y'),
                            'time'  => $created->format('h:i A'),
                        ],
                    ];

                case 'sites':
                    $item = Site::find($cloud->item_id);
                    if (!$item) return null;
                    return [
                        'cloud_id'    => $cloud->id,
                        'item_type'   => $cloud->item_type,
                        'item_id'     => $cloud->item_id,
                        'id'          => $item->id,
                        'title'       => $item->title,
                        'slug'        => $item->slug,
                        'is_premium'  => $item->is_premium,
                        'image'       => $item->image,
                        'dateAndTime' => [
                            'day'   => $created->format('j'),
                            'month' => $created->format('F'),
                            'year'  => $created->format('Y'),
                            'time'  => $created->format('h:i A'),
                        ],
                    ];

                case 'pages':
                    $item = Page::find($cloud->item_id);
                    if (!$item) return null;
                    return [
                        'cloud_id'    => $cloud->id,
                        'item_type'   => $cloud->item_type,
                        'item_id'     => $cloud->item_id,
                        'id'          => $item->id,
                        'title'       => $item->title,
                        'slug'        => $item->slug,
                        'is_premium'  => $item->is_premium,
                        'image'       => $item->image,
                        'dateAndTime' => [
                            'day'   => $created->format('j'),
                            'month' => $created->format('F'),
                            'year'  => $created->format('Y'),
                            'time'  => $created->format('h:i A'),
                        ],
                    ];

                default:
                    return null;
            }
        })->filter();

        // Return the response in the desired JSON format
        return response()->json([
            'categories' => [],
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

    public function remove_item(Request $request): JsonResponse 
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'item_id' => 'required|string',
            'item_type' => 'required|string',
            'license_key' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $license = License::where('license_key', $request->license_key)->first();

        if (!$license) {
            return response()->json([
                'status' => 'error',
                'message' => 'License not found',
            ], 404);
        }

        $user = $license->user;
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user associated with this license',
            ], 404);
        }

        // Sub user (by email)
        $sub_user = User::where('email', $request->email)->first();
        if (!$sub_user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User with this email not found',
            ], 404);
        }

        // Build delete query
        $query = Cloud::where('user_id', $user->id)
            ->where('item_type', $request->item_type)
            ->where('item_id', intval($request->item_id));

        if ($sub_user->id === $user->id) {
            // Main user → only his own saved item
            $query->whereNull('sub_user');
        } else {
            // Sub user → only his own saved item
            $query->where('sub_user', $sub_user->id);
        }

        $cloudItem = $query->first();

        if (!$cloudItem) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Item not found or not owned by this user',
            ], 404);
        }

        // Delete the item
        $cloudItem->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Item removed successfully',
        ]);
    }
}
