<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Pet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PetFeedingWeekController extends Controller
{
    public function show(Request $request, string $householdId, string $petId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        /** @var Pet $pet */
        $pet = $household->pets()->findOrFail($petId);

        $start = $request->query('start');
        $weekStart = $start
            ? Carbon::parse($start)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $plan = $pet->feedingPlans()->with('slots')->first();
        $slots = $plan ? $plan->slots->where('is_active', true) : collect();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $dow = (int) $date->isoWeekday();

            $expectedSlotIds = $slots
                ->filter(fn ($s) => in_array($dow, $s->weekdays ?? [], true))
                ->pluck('id')
                ->values();

            $logsQuery = ActivityLog::query()
                ->where('pet_id', $pet->id)
                ->whereDate('started_at', $date->format('Y-m-d'));

            $bySlot = (clone $logsQuery)
                ->whereNotNull('feeding_plan_slot_id')
                ->pluck('feeding_plan_slot_id')
                ->unique()
                ->values();

            $expectedIds = $expectedSlotIds->all();
            $completedFromSlot = $bySlot->filter(fn ($id) => in_array($id, $expectedIds, true))->values()->all();

            // Fallback: same activity type on this day counts toward slots missing slot_id (legacy logs)
            $fallbackCompleted = [];
            if ($expectedSlotIds->isNotEmpty()) {
                $slotModels = $slots->whereIn('id', $expectedIds);
                foreach ($slotModels as $slot) {
                    if (in_array($slot->id, $completedFromSlot, true)) {
                        continue;
                    }
                    $exists = (clone $logsQuery)
                        ->whereNull('feeding_plan_slot_id')
                        ->where('activity_type_id', $slot->activity_type_id)
                        ->exists();
                    if ($exists) {
                        $fallbackCompleted[] = $slot->id;
                    }
                }
            }

            $completedSlotIds = array_values(array_unique(array_merge($completedFromSlot, $fallbackCompleted)));

            $days[] = [
                'date' => $date->format('Y-m-d'),
                'expected_slot_ids' => $expectedIds,
                'completed_slot_ids' => $completedSlotIds,
            ];
        }

        return response()->json([
            'week_start' => $weekStart->format('Y-m-d'),
            'days' => $days,
        ]);
    }
}
