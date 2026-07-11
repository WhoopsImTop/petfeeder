<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeederWebhookRequest;
use App\Models\Household;
use App\Services\FeederWebhookService;
use Illuminate\Http\JsonResponse;

class FeederWebhookController extends Controller
{
    public function __construct(
        private readonly FeederWebhookService $webhookService,
    ) {}

    public function store(StoreFeederWebhookRequest $request, string $token): JsonResponse
    {
        $household = Household::query()
            ->where('feeder_webhook_token', $token)
            ->where('feeder_webhook_enabled', true)
            ->firstOrFail();

        $feederEvent = $this->webhookService->process(
            $household,
            $request->validated(),
            $request->file('image'),
        );

        return response()->json([
            'id' => $feederEvent->id,
            'detected_at' => $feederEvent->detected_at,
            'action' => $feederEvent->action,
        ], 201);
    }
}
