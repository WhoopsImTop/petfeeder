<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedingPlanRequest;
use App\Http\Requests\UpdateFeedingPlanRequest;
use App\Models\FeedingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedingPlanController extends Controller
{
    public function index(Request $request, string $householdId)
    {
        $household = $request->user()->households()->findOrFail($householdId);

        return response()->json(
            $household->feedingPlans()->with(['slots.activityType', 'pets'])->orderBy('name')->get()
        );
    }

    public function store(StoreFeedingPlanRequest $request, string $householdId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $validated = $request->validated();

        $plan = DB::transaction(function () use ($household, $householdId, $validated) {
            $plan = FeedingPlan::create([
                'household_id' => $household->id,
                'name' => $validated['name'],
            ]);

            $this->syncPets($plan, $validated['pet_ids'] ?? []);

            foreach ($validated['slots'] ?? [] as $slot) {
                $plan->slots()->create([
                    'activity_type_id' => $slot['activity_type_id'],
                    'title' => $slot['title'] ?? null,
                    'time' => $slot['time'].':00',
                    'weekdays' => array_values(array_map('intval', $slot['weekdays'])),
                    'is_active' => $slot['is_active'] ?? true,
                ]);
            }

            return $plan;
        });

        return response()->json($plan->load(['slots.activityType', 'pets']), 201);
    }

    public function show(Request $request, string $householdId, FeedingPlan $feedingPlan)
    {
        $this->assertPlanHousehold($request, $householdId, $feedingPlan);

        return response()->json($feedingPlan->load(['slots.activityType', 'pets']));
    }

    public function update(UpdateFeedingPlanRequest $request, string $householdId, FeedingPlan $feedingPlan)
    {
        $this->assertPlanHousehold($request, $householdId, $feedingPlan);
        $validated = $request->validated();

        DB::transaction(function () use ($feedingPlan, $validated) {
            if (array_key_exists('name', $validated)) {
                $feedingPlan->update(['name' => $validated['name']]);
            }

            if (array_key_exists('pet_ids', $validated)) {
                $this->syncPets($feedingPlan, $validated['pet_ids']);
            }

            if (array_key_exists('slots', $validated)) {
                $this->syncSlots($feedingPlan, $validated['slots']);
            }
        });

        return response()->json($feedingPlan->fresh()->load(['slots.activityType', 'pets']));
    }

    public function destroy(Request $request, string $householdId, FeedingPlan $feedingPlan)
    {
        $this->assertPlanHousehold($request, $householdId, $feedingPlan);
        $feedingPlan->delete();

        return response()->json(null, 204);
    }

    private function assertPlanHousehold(Request $request, string $householdId, FeedingPlan $feedingPlan): void
    {
        $household = $request->user()->households()->findOrFail($householdId);
        if ((int) $feedingPlan->household_id !== (int) $household->id) {
            abort(404);
        }
    }

    /**
     * @param  array<int>  $petIds
     */
    private function syncPets(FeedingPlan $plan, array $petIds): void
    {
        $petIds = array_values(array_unique(array_map('intval', $petIds)));
        foreach ($petIds as $petId) {
            DB::table('feeding_plan_pet')->where('pet_id', $petId)->delete();
        }
        if ($petIds !== []) {
            $plan->pets()->attach($petIds);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $slots
     */
    private function syncSlots(FeedingPlan $plan, array $slots): void
    {
        $incomingIds = collect($slots)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

        $plan->slots()->whereNotIn('id', $incomingIds)->delete();

        foreach ($slots as $slot) {
            $time = strlen((string) $slot['time']) === 5 ? $slot['time'].':00' : $slot['time'];
            $payload = [
                'activity_type_id' => $slot['activity_type_id'],
                'title' => $slot['title'] ?? null,
                'time' => $time,
                'weekdays' => array_values(array_map('intval', $slot['weekdays'])),
                'is_active' => $slot['is_active'] ?? true,
            ];

            if (! empty($slot['id'])) {
                $plan->slots()->where('id', (int) $slot['id'])->update($payload);
            } else {
                $plan->slots()->create($payload);
            }
        }
    }
}
