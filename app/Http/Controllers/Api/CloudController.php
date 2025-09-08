<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cloud;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class CloudController extends Controller
{
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
            'item_id' => 'nullable|integer',
            'item_type' => 'required|string|max:255',
            'data' => 'required|json',
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

        // Create a new Cloud record
        $cloud = Cloud::create([
            'user_id' => $user->id,
            'item_id' => $request->input('item_id'),
            'item_type' => $request->input('item_type'),
            'data' => $request->input('data'),
            'website' => $request->input('website'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Item saved successfully',
            'data' => $cloud,
        ], 201);
    }
}
