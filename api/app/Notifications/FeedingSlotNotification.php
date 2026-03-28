<?php

namespace App\Notifications;

use App\Models\FeedingPlanSlot;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class FeedingSlotNotification extends Notification
{
    use Queueable;

    /**
     * @param  Collection<int, \App\Models\Pet>  $pets
     */
    public function __construct(
        public FeedingPlanSlot $slot,
        public Collection $pets,
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $petNames = $this->pets->pluck('name')->unique()->values()->implode(', ');
        $label = $this->slot->title ?: $this->slot->activityType->name;

        $title = $this->pets->count() > 1
            ? 'Füttern: '.$petNames
            : 'Füttern: '.$this->pets->first()->name;

        $body = 'Zeit für '.$label.'.';

        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->action('App öffnen', config('app.url'))
            ->data([
                'feeding_plan_slot_id' => $this->slot->id,
                'feeding_plan_id' => $this->slot->feeding_plan_id,
                'feeding_plan_slot_ids' => [$this->slot->id],
                'reminder_id' => $this->slot->id,
                'reminder_ids' => [$this->slot->id],
            ]);
    }
}
