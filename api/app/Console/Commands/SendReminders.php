<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Reminder;
use App\Notifications\ReminderNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

#[Signature('app:send-reminders')]
#[Description('Sends push notifications for active reminders')]
class SendReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentTime = now()->format('H:i');

        $reminders = Reminder::with(['pet', 'activityType', 'household.users'])
            ->where('is_active', true)
            ->where('time', 'like', $currentTime . ':%')
            ->get();

        /** @var Collection<string, Collection<int, Reminder>> */
        $buckets = $reminders->groupBy(function (Reminder $reminder) {
            return $reminder->reminder_group_id
                ? 'group:'.$reminder->reminder_group_id
                : 'single:'.$reminder->id;
        });

        $count = 0;

        foreach ($buckets as $group) {
            $first = $group->first();
            $members = $first->household->users;

            if ($members->isNotEmpty()) {
                Notification::send($members, new ReminderNotification($first, $group));
                $count++;
            }
        }

        $this->info("Sent {$count} reminders.");
    }
}
