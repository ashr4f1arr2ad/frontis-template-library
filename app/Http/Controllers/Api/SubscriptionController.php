<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\License;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SubscriptionController extends Controller
{
    public function update(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'email' => 'required|string|email|max:255', // Removed unique constraint
            'subscription.title' => 'nullable|string|max:255',
            'subscription.type' => 'nullable|string',
            'subscription.total_sites' => 'nullable|integer|min:1',
            'subscription.total_users' => 'nullable|integer|min:0',
            'subscription.license_key' => 'nullable|string',
            'subscription.license_expiry' => 'nullable|date',
            'subscription.id' => 'nullable|integer|exists:subscriptions,id', // Optional subscription ID for updating specific subscription
        ]);

        // Find the user by email
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Check if subscription data is provided
        if (!empty($validated['subscription']['title']) || !empty($validated['subscription']['id'])) {
            // If a subscription ID is provided, try to update an existing subscription
            if (!empty($validated['subscription']['id'])) {
                $subscription = $user->subscriptions()->where('subscriptions.id', $validated['subscription']['id'])->first();

                if ($subscription) {
                    // Update the existing subscription
                    $subscription->update([
                        'title' => $validated['subscription']['title'] ?? $subscription->title,
                        'type' => $validated['subscription']['type'] ?? $subscription->type,
                        'total_sites' => $validated['subscription']['total_sites'] ?? $subscription->total_sites,
                        'total_users' => $validated['subscription']['total_users'] ?? $subscription->total_users,
                        'license_key' => $validated['subscription']['license_key'] ?? $subscription->license_key,
                        'license_expiry' => $validated['subscription']['license_expiry'] ?? $subscription->license_expiry,
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => 'Subscription updated successfully',
                        'user' => $user->load('subscriptions'),
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Subscription not found for this user',
                    ], 404);
                }
            }

            // If no subscription ID is provided, create a new subscription
            $subscription = Subscription::create([
                'title' => $validated['subscription']['title'] ?? 'Default Subscription',
                'type' => $validated['subscription']['type'] ?? null,
                'total_sites' => $validated['subscription']['total_sites'] ?? 1,
                'total_users' => $validated['subscription']['total_users'] ?? 0,
                'license_key' => $validated['subscription']['license_key'] ?? null,
                'license_expiry' => $validated['subscription']['license_expiry'] ?? null,
            ]);

            // Attach the new subscription to the user
            $user->subscriptions()->attach($subscription->id);

            return response()->json([
                'status' => true,
                'message' => 'Subscription created and assigned successfully',
                'user' => $user->load('subscriptions'),
            ], 201);
        }

        return response()->json([
            'status' => true,
            'message' => 'No subscription data provided, user details unchanged',
            'user' => $user->load('subscriptions'),
        ], 200);
    }

    public function upgrade(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the request
        $validated = $request->validate([
            'email' => 'required|string|email|max:255', // Removed unique constraint
            'title' => 'nullable|string|max:255',
            'type' => 'nullable|string',
            'license_key' => 'nullable|string',
            'total_users' => 'required|integer|min:1',
            'expire_date' => 'required|string|date',
        ]);


        // Find the user by email
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        $newSubscription = Subscription::create([
            'title' => $validated['title'] ?? null,
            'type' => $validated['type'] ?? null,
            'total_users' => $validated['total_users'] ?? 0,
            'expire_date' => $validated['expire_date'] ?? null,
        ]);

        $user->subscriptions()->attach($newSubscription->id);

        if (!empty($validated['license_key']) && !$user->license_id) {
            $license = License::create([
                'license_key' => $validated['license_key'] ?? null
            ]);

            // Update user's license_id column
            $user->update([
                'license_id' => $license->id
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Subscription Added Successfully',
        ], 200);
    }

    public function upgrade_subscription(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the request
        $validated = $request->validate([
            'email' => 'required|string|email|max:255', // Removed unique constraint
            'title' => 'nullable|string|max:255',
            'type' => 'nullable|string',
            'license_key' => 'nullable|string',
            'total_users' => 'required|integer|min:1',
            'expire_date' => 'required|string',
        ]);


        dd($validated);
    }
}
