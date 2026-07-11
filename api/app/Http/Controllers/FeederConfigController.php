<?php

namespace App\Http\Controllers;

use App\Models\FeederEvent;
use App\Services\FeederConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FeederConfigController extends Controller
{
    public function __construct(
        private readonly FeederConfigService $configService,
    ) {}

    public function show(Request $request, string $householdId): JsonResponse
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $household = $this->configService->ensureWebhookToken($household);
        $household = $this->configService->ensureDefaultActivityTypes($household);

        return response()->json($this->configService->toConfigArray($household));
    }

    public function update(Request $request, string $householdId): JsonResponse
    {
        $household = $request->user()->households()->wherePivot('role', 'admin')->findOrFail($householdId);

        $validated = $request->validate([
            'feeder_webhook_enabled' => ['sometimes', 'boolean'],
            'feeder_action_open_activity_type_id' => [
                'nullable', 'integer',
                Rule::exists('activity_types', 'id')->where('household_id', $householdId),
            ],
            'feeder_action_stay_closed_activity_type_id' => [
                'nullable', 'integer',
                Rule::exists('activity_types', 'id')->where('household_id', $householdId),
            ],
            'feeder_action_none_activity_type_id' => [
                'nullable', 'integer',
                Rule::exists('activity_types', 'id')->where('household_id', $householdId),
            ],
        ]);

        $household = $this->configService->ensureWebhookToken($household);
        $household->update($validated);

        return response()->json($this->configService->toConfigArray($household->fresh()));
    }

    public function regenerateToken(Request $request, string $householdId): JsonResponse
    {
        $household = $request->user()->households()->wherePivot('role', 'admin')->findOrFail($householdId);

        $household->update([
            'feeder_webhook_token' => Str::random(64),
        ]);

        $household = $this->configService->ensureDefaultActivityTypes($household->fresh());

        return response()->json($this->configService->toConfigArray($household));
    }
}
