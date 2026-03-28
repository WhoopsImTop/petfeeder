<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use App\Models\ActivityLog;
use Illuminate\Support\Collection;

class ActivityLoggedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ActivityLog $activityLog,
        public ?Collection $groupActivityLogs = null,
    ) {
        if ($this->groupActivityLogs === null) {
            $this->groupActivityLogs = collect([$this->activityLog]);
        }
    }

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    private function joinPetNames(array $names, string $conjunction = 'und'): string
    {
        $names = array_values(array_filter($names));
        $count = count($names);

        if ($count === 0) return '';
        if ($count === 1) return (string) $names[0];
        if ($count === 2) return $names[0] . ' ' . $conjunction . ' ' . $names[1];

        return implode(', ', array_slice($names, 0, -1)) . ' ' . $conjunction . ' ' . $names[$count - 1];
    }

    private function isFeedingActivity(): bool
    {
        $name = (string) ($this->activityLog->activityType->name ?? '');
        $normalized = function_exists('mb_strtolower')
            ? mb_strtolower($name, 'UTF-8')
            : strtolower($name);

        // Normalize German umlauts to keep regex simple
        $normalized = str_replace(['Ü', 'ü'], 'u', $normalized);

        return (bool) preg_match('/(futter|fuet|feed|feeding)/i', $normalized);
    }

    public function toWebPush($notifiable, $notification)
    {
        $petNames = $this->groupActivityLogs
            ->map(fn (ActivityLog $log) => $log->pet->name)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $petNamesText = $this->joinPetNames($petNames);
        $actorName = (string) ($this->activityLog->user->name ?? 'Jemand');
        $activityTypeName = (string) ($this->activityLog->activityType->name ?? 'Aktion');

        $activityLogIds = $this->groupActivityLogs
            ->pluck('id')
            ->values()
            ->all();

        if ($this->isFeedingActivity()) {
            if (count($petNames) > 1) {
                $petNamesTextEn = $this->joinPetNames($petNames, 'and');
                return (new WebPushMessage)
                    ->title('Fed: ' . $petNamesTextEn)
                    ->body($petNamesTextEn . ' were fed')
                    ->action('View App', config('app.url'))
                    ->data([
                        'activity_log_id' => $this->activityLog->id,
                        'activity_log_ids' => $activityLogIds,
                    ]);
            }

            $single = $petNames[0] ?? 'Dein Tier';
            $singleEn = $single;

            return (new WebPushMessage)
                ->title('Fed: ' . $singleEn)
                ->body($singleEn . ' was fed')
                ->action('View App', config('app.url'))
                ->data([
                    'activity_log_id' => $this->activityLog->id,
                    'activity_log_ids' => $activityLogIds,
                ]);
        }

        // Fallback: group summary + actor/activity type
        $verb = count($petNames) > 1 ? 'protokolliert' : 'protokolliert';
        return (new WebPushMessage)
            ->title('Neue Aktivitaet: ' . $petNamesText)
            ->body($actorName . ' hat ' . $petNamesText . ' ' . $verb . ' (' . $activityTypeName . ').')
            ->action('View App', config('app.url'))
            ->data([
                'activity_log_id' => $this->activityLog->id,
                'activity_log_ids' => $activityLogIds,
            ]);
    }
}
