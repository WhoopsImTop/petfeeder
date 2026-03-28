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
}
