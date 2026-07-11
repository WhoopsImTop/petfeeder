<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\FeederEvent;
use App\Models\Household;
use App\Notifications\FeederPreyDetectedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class FeederWebhookService
{
    public function __construct(
        private readonly FeederConfigService $configService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function process(Household $household, array $data, ?UploadedFile $image = null): FeederEvent
    {
        $household = $this->configService->ensureDefaultActivityTypes($household);

        return DB::transaction(function () use ($household, $data, $image) {
            $imagePath = null;
            if ($image) {
                $imagePath = $image->store('feeder-events/'.$household->id, 'public');
            }

            $feederEvent = FeederEvent::create([
                'household_id' => $household->id,
                'detected_at' => $data['timestamp'],
                'label' => $data['label'],
                'action' => $data['action'],
                'confidence' => $data['confidence'],
                'mouth_status' => $data['mouth_status'] ?? null,
                'detections' => $data['detections'],
                'image_path' => $imagePath,
            ]);

            $activityTypeId = $this->configService->activityTypeIdForAction($household, $data['action']);

            if ($activityTypeId) {
                $activityLog = ActivityLog::create([
                    'household_id' => $household->id,
                    'pet_id' => null,
                    'activity_type_id' => $activityTypeId,
                    'feeder_event_id' => $feederEvent->id,
                    'user_id' => null,
                    'started_at' => $data['timestamp'],
                    'notes' => $this->buildNotes($data),
                ]);

                $feederEvent->update(['activity_log_id' => $activityLog->id]);
            }

            if ($data['action'] === 'stay_closed') {
                $members = $household->users()->get();
                if ($members->isNotEmpty()) {
                    Notification::send($members, new FeederPreyDetectedNotification($feederEvent->fresh()));
                }
            }

            return $feederEvent->fresh(['activityLog']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildNotes(array $data): string
    {
        $parts = [
            'label: '.$data['label'],
            'confidence: '.round((float) $data['confidence'] * 100, 1).'%',
        ];

        if (! empty($data['mouth_status'])) {
            $parts[] = 'mouth_status: '.$data['mouth_status'];
        }

        return implode(', ', $parts);
    }
}
