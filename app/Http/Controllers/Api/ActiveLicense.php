<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\License;
use App\Models\User;
use Carbon\Carbon;

class ActiveLicense extends Controller
{
    public function activate(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the request inputs
        $validated = $request->validate([
            'license_key' => 'required|string',
            'website_url' => 'required|url',
        ]);

        // Find the license by license_key
        $license = License::where('license_key', $validated['license_key'])->first();

        // Check if license exists
        if (!$license) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid license key.',
            ], 404);
        }

        // Get the user associated with the license
        $user = $license->user;

        // Check if user exists
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user associated with this license.',
            ], 404);
        }

        // Find an active subscription
        $subscription = $user->subscriptions()
            ->where('expire_date', '>=', Carbon::today())
            ->where('status', 1)
            ->first();

        // Check if an active subscription exists
        if (!$subscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active subscriptions found for this license.',
            ], 400);
        }

        // Get the sites JSON field (cast to array)
        $sites = json_decode($subscription->sites, true) ?? [];

        // Check if the website_url is already in the sites array
        if (in_array($validated['website_url'], $sites)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Website is already activated for this subscription.',
                'user_id' => $user->id,
                'site_count' => count($sites),
                'total_allowed_sites' => $subscription->total_users,
                'total_sites_used' => $subscription->total_used_sites,
            ], 200);
        }

        // Check if adding the new site exceeds the total_used_sites limit
        if ($subscription->total_used_sites >= $subscription->total_users) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot add website: Maximum site limit reached.',
            ], 400);
        }

        // Add the new website_url to the sites array
        $sites[] = $validated['website_url'];

        // Update the subscription with the new sites array and increment total_sites_used
        $subscription->update([
            'sites' => json_encode($sites),
            'total_used_sites' => $subscription->total_used_sites + 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Website successfully activated for the subscription.',
            'user_id' => $user->id,
            'site_count' => count($sites),
            'total_allowed_sites' => $subscription->total_users,
            'total_sites_used' => $subscription->total_used_sites,
        ], 200);
    }
}
