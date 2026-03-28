<?php

namespace App\Notifications;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ReminderNotification extends Notification
{
    use Queueable;

    /**
     * @param  Collection<int, Reminder>|null  $groupReminders  All reminders in the same scheduled group, or null for a single reminder.
     */
    public function __construct(
        public Reminder $reminder,
        public ?Collection $groupReminders = null,
    ) {
        if ($this->groupReminders === null) {
            $this->groupReminders = collect([$this->reminder]);
        }
    }

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $petNames = $this->groupReminders
            ->map(fn (Reminder $r) => $r->pet->name)
            ->unique()
            ->values()
            ->implode(', ');

        $title = $this->groupReminders->count() > 1
            ? 'Erinnerung: '.$petNames
            : 'Erinnerung: '.$this->reminder->pet->name;

        $body = 'Zeit für '.$this->reminder->title.' ('.$this->reminder->activityType->name.').';

        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->action('App öffnen', config('app.url'))
            ->data([
                'reminder_id' => $this->reminder->id,
                'reminder_group_id' => $this->reminder->reminder_group_id,
                'reminder_ids' => $this->groupReminders->pluck('id')->values()->all(),
            ]);
    }
}
