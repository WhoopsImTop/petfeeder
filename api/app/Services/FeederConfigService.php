<?php

namespace App\Services;

use App\Models\ActivityType;
use App\Models\Household;
use Illuminate\Support\Str;

class FeederConfigService
{
    public function ensureWebhookToken(Household $household): Household
    {
        if ($household->feeder_webhook_token) {
            return $household;
        }

        $household->update([
            'feeder_webhook_token' => Str::random(64),
        ]);

        return $household->fresh();
    }

    public function ensureDefaultActivityTypes(Household $household): Household
    {
        $defaults = [
            'open' => ['name' => 'Futterautomat: Geöffnet', 'icon' => '🟢'],
            'stay_closed' => ['name' => 'Futterautomat: Beute erkannt', 'icon' => '🦎'],
            'none' => ['name' => 'Futterautomat: Keine Aktion', 'icon' => '⚪'],
        ];

        $updates = [];

        foreach ($defaults as $action => $meta) {
            $column = "feeder_action_{$action}_activity_type_id";

            if ($household->{$column}) {
                continue;
            }

            $type = ActivityType::create([
                'household_id' => $household->id,
                'name' => $meta['name'],
                'type' => 'boolean',
                'icon' => $meta['icon'],
                'is_fast_action' => false,
            ]);

            $updates[$column] = $type->id;
        }

        if ($updates !== []) {
            $household->update($updates);
            $household = $household->fresh();
        }

        return $household;
    }

    public function activityTypeIdForAction(Household $household, string $action): ?int
    {
        return match ($action) {
            'open' => $household->feeder_action_open_activity_type_id,
            'stay_closed' => $household->feeder_action_stay_closed_activity_type_id,
            'none' => $household->feeder_action_none_activity_type_id,
            default => null,
        };
    }

    public function webhookUrl(Household $household): ?string
    {
        if (! $household->feeder_webhook_token) {
            return null;
        }

        return rtrim((string) config('app.url'), '/').'/api/webhooks/feeder/'.$household->feeder_webhook_token;
    }

    public function toConfigArray(Household $household): array
    {
        return [
            'feeder_webhook_enabled' => (bool) $household->feeder_webhook_enabled,
            'webhook_url' => $this->webhookUrl($household),
            'feeder_action_open_activity_type_id' => $household->feeder_action_open_activity_type_id,
            'feeder_action_stay_closed_activity_type_id' => $household->feeder_action_stay_closed_activity_type_id,
            'feeder_action_none_activity_type_id' => $household->feeder_action_none_activity_type_id,
        ];
    }
}
