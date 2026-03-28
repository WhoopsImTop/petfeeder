<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\FeedingPlanSlot;
use App\Notifications\FeedingSlotNotification;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('app:send-reminders')]
#[Description('Sends push notifications for active feeding plan slots')]
class SendReminders extends Command
{
    public function handle(): int
    {
        $currentTime = now()->format('H:i');
        $isoDow = (int) now()->isoWeekday();

        $slots = FeedingPlanSlot::query()
            ->with(['feedingPlan.household.users', 'feedingPlan.pets', 'activityType'])
            ->where('is_active', true)
            ->where('time', 'like', $currentTime.':%')
            ->get()
            ->filter(fn (FeedingPlanSlot $s) => in_array($isoDow, $s->weekdays ?? [], true));

        $buckets = $slots->groupBy(
            fn (FeedingPlanSlot $s) => $s->feeding_plan_id.'|'.$s->time.'|'.$s->activity_type_id
        );

        $count = 0;

        foreach ($buckets as $group) {
            /** @var FeedingPlanSlot $slot */
            $slot = $group->first();
            $household = $slot->feedingPlan->household;
            $pets = $slot->feedingPlan->pets;

            if ($pets->isEmpty()) {
                continue;
            }

            $due = Carbon::parse(now()->format('Y-m-d').' '.$this->normalizeTime($slot->time));

            $allDone = true;
            foreach ($pets as $pet) {
                if (! $this->petFedBeforeDue($pet->id, $slot, $due)) {
                    $allDone = false;
                    break;
                }
            }

            if ($allDone) {
                continue;
            }

            $members = $household->users;
            if ($members->isNotEmpty()) {
                Notification::send($members, new FeedingSlotNotification($slot, $pets));
                $count++;
            }
        }

        $this->info("Sent {$count} feeding reminders.");

        return self::SUCCESS;
    }

    private function normalizeTime(mixed $time): string
    {
        if (is_string($time)) {
            return strlen($time) === 5 ? $time.':00' : $time;
        }

        return (string) $time;
    }

    private function petFedBeforeDue(int $petId, FeedingPlanSlot $slot, Carbon $due): bool
    {
        $today = $due->format('Y-m-d');

        $bySlot = ActivityLog::query()
            ->where('pet_id', $petId)
            ->where('feeding_plan_slot_id', $slot->id)
            ->whereDate('started_at', $today)
            ->where('started_at', '<', $due)
            ->exists();

        if ($bySlot) {
            return true;
        }

        return ActivityLog::query()
            ->where('pet_id', $petId)
            ->whereNull('feeding_plan_slot_id')
            ->where('activity_type_id', $slot->activity_type_id)
            ->whereDate('started_at', $today)
            ->where('started_at', '<', $due)
            ->exists();
    }
}
