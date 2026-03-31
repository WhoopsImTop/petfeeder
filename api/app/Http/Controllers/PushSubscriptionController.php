<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\StorePushSubscriptionRequest;

class PushSubscriptionController extends Controller
{
    public function store(StorePushSubscriptionRequest $request)
    {
        $validated = $request->validated();

        $endpoint = $validated['endpoint'];
        $hash = hash('sha256', $endpoint);

        $request->user()->pushSubscriptions()->updateOrCreate(
            ['endpoint_hash' => $hash],
            [
                'endpoint' => $endpoint,
                'public_key' => $validated['keys']['p256dh'] ?? null,
                'auth_token' => $validated['keys']['auth'] ?? null,
                'content_encoding' => $validated['contentEncoding'] ?? null,
            ]
        );

        return response()->json(['message' => 'Subscription created successfully.'], 201);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'nullable|string',
        ]);

        if (!empty($validated['endpoint'])) {
            $hash = hash('sha256', $validated['endpoint']);
            $request->user()->pushSubscriptions()->where('endpoint_hash', $hash)->delete();

            return response()->json(['message' => 'Subscription removed successfully.']);
        }

        $request->user()->pushSubscriptions()->delete();
        return response()->json(['message' => 'All subscriptions removed successfully.']);
    }
}
