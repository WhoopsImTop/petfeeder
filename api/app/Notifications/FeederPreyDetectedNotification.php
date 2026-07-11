<?php

namespace App\Notifications;

use App\Models\FeederEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class FeederPreyDetectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public FeederEvent $feederEvent,
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $confidencePercent = round((float) $this->feederEvent->confidence * 100, 1);
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        $data = [
            'feeder_event_id' => $this->feederEvent->id,
            'url' => $frontendUrl.'/feeder',
        ];

        if ($this->feederEvent->image_url) {
            $data['image'] = $this->feederEvent->image_url;
        }

        return (new WebPushMessage)
            ->title('Beute erkannt')
            ->body($this->feederEvent->label.' ('.$confidencePercent.'% Confidence)')
            ->action('Anzeigen', $frontendUrl.'/feeder')
            ->data($data);
    }
}
