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
            'license_key' => 'required|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'type' => 'nullable|string',
            'search' => 'nullable|string'
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
            $query->whereNull('sub_user')
                ->orWhere('sub_user', $user->id); // Main user
        } else {
            $query->where(function ($q) use ($sub_user) {
                $q->whereNull('sub_user') // main user items
                  ->orWhere('sub_user', $sub_user->id); // sub-user items
            });
        }

        $page = $request->input('page', 1); // default page = 1
        $perPage = $request->input('per_page', 12);

        if ($request->filled('type')) {
            $type = $request->input('type');
            $query->where('item_type', 'like', '%' . $type . '%');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('item_type', 'like', '%' . $search . '%');
        }

        $clouds = $query->paginate($perPage, ['*'], 'page', $page);
        $clouds->getCollection()->transform(function ($cloud) {
            $created = Carbon::parse($cloud->created_at);
        
            switch ($cloud->item_type) {
                case 'patterns':
                    $item = Pattern::find($cloud->item_id);
                    break;
                case 'sites':
                    $item = Site::find($cloud->item_id);
                    break;
                case 'pages':
                    $item = Page::find($cloud->item_id);
                    break;
                default:
                    $item = null;
            }
        
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
        });
        
        $clouds->setCollection($clouds->getCollection()->filter());

        $categories = [
            [
                "slug" => "my-cloud",
                "name" => "My Cloud",
                "icon" => "<svg xmlns='http://www.w3.org/2000/svg' wslugth='20' height='20' viewBox='0 0 20 20' fill='none'><path d='M3.125 5C3.125 3.96447 3.96447 3.125 5 3.125H6.875C7.91053 3.125 8.75 3.96447 8.75 5V6.875C8.75 7.91053 7.91053 8.75 6.875 8.75H5C3.96447 8.75 3.125 7.91053 3.125 6.875V5Z' stroke='#404655' stroke-wslugth='1.5' stroke-linecap='round' stroke-linejoin='round'/><path d='M3.125 13.125C3.125 12.0895 3.96447 11.25 5 11.25H6.875C7.91053 11.25 8.75 12.0895 8.75 13.125V15C8.75 16.0355 7.91053 16.875 6.875 16.875H5C3.96447 16.875 3.125 16.0355 3.125 15V13.125Z' stroke='#404655' stroke-wslugth='1.5' stroke-linecap='round' stroke-linejoin='round'/><path d='M11.25 5C11.25 3.96447 12.0895 3.125 13.125 3.125H15C16.0355 3.125 16.875 3.96447 16.875 5V6.875C16.875 7.91053 16.0355 8.75 15 8.75H13.125C12.0895 8.75 11.25 7.91053 11.25 6.875V5Z' stroke='#404655' stroke-wslugth='1.5' stroke-linecap='round' stroke-linejoin='round'/><path d='M11.25 13.125C11.25 12.0895 12.0895 11.25 13.125 11.25H15C16.0355 11.25 16.875 12.0895 16.875 13.125V15C16.875 16.0355 16.0355 16.875 15 16.875H13.125C12.0895 16.875 11.25 16.0355 11.25 15V13.125Z' stroke='#404655' stroke-wslugth='1.5' stroke-linecap='round' stroke-linejoin='round'/></svg>",
                "count" => []
            ],
            [
                "slug" => "my-downloads",
                "name" => "My Downloads",
                "icon" => "<svg xmlns='http://www.w3.org/2000/svg' wslugth='24' height='24' viewBox='0 0 24 24' fill='none'><path d='M3.09477 10C3.03217 10.457 2.99976 10.9245 2.99976 11.4C2.99976 16.7019 7.02919 21 11.9998 21C16.9703 21 20.9998 16.7019 20.9998 11.4C20.9998 10.9245 20.9673 10.457 20.9047 10' stroke='#404655' stroke-wslugth='1.25' stroke-linecap='round'/><path d='M11.9998 13V3M11.9998 13C11.2995 13 9.99129 11.0057 9.49976 10.5M11.9998 13C12.7 13 14.0082 11.0057 14.4998 10.5' stroke='#404655' stroke-wslugth='1.25' stroke-linecap='round' stroke-linejoin='round'/></svg>",
                "count" => []
            ],
            [
                "slug" => "shared-with-me",
                "name" => "Shared with me",
                "icon" => "<svg xmlns='http://www.w3.org/2000/svg' wslugth='24' height='24' viewBox='0 0 24 24' fill='none'><path d='M20.5 5.5C20.5 7.15685 19.1569 8.5 17.5 8.5C15.8431 8.5 14.5 7.15685 14.5 5.5C14.5 3.84315 15.8431 2.5 17.5 2.5C19.1569 2.5 20.5 3.84315 20.5 5.5Z' stroke='#404655' stroke-wslugth='1.25'/><path d='M8.5 11.5C8.5 13.1569 7.15685 14.5 5.5 14.5C3.84315 14.5 2.5 13.1569 2.5 11.5C2.5 9.84315 3.84315 8.5 5.5 8.5C7.15685 8.5 8.5 9.84315 8.5 11.5Z' stroke='#404655' stroke-wslugth='1.25'/><path d='M21.5 18.5C21.5 20.1569 20.1569 21.5 18.5 21.5C16.8431 21.5 15.5 20.1569 15.5 18.5C15.5 16.8431 16.8431 15.5 18.5 15.5C20.1569 15.5 21.5 16.8431 21.5 18.5Z' stroke='#404655' stroke-wslugth='1.25'/><path d='M14.5348 4.58109C14.1554 4.52765 13.7677 4.5 13.3733 4.5C10.2974 4.5 7.62058 6.18227 6.24054 8.66317M19.7131 7.49453C20.8311 8.86497 21.5 10.6056 21.5 12.5C21.5 13.8758 21.1472 15.1705 20.5258 16.3012M15.8816 20.1117C15.0917 20.3638 14.2486 20.5 13.3733 20.5C9.58287 20.5 6.39853 17.9454 5.5 14.4898' stroke='#404655' stroke-wslugth='1.25'/></svg>",
                "count" => []
            ],
        ];

        // Return the response in the desired JSON format
        return response()->json([
            'categories' => $categories,
            'items' => $clouds->items(),
            'pagination' => [
                'current_page' => $clouds->currentPage(),
                'per_page' => $clouds->perPage(),
                'total' => $clouds->total(),
                'last_page' => $clouds->lastPage(),
                'from' => $clouds->firstItem(),
                'to' => $clouds->lastItem(),
            ]
        ]);
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
