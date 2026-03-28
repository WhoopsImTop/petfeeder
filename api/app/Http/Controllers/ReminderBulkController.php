<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBulkRemindersRequest;
use App\Models\Reminder;
use Illuminate\Support\Str;

class ReminderBulkController extends Controller
{
    public function store(StoreBulkRemindersRequest $request, string $householdId)
    {
        $validated = $request->validated();
        $petIds = array_values(array_unique($validated['pet_ids']));
        $groupId = (string) Str::uuid();

        $created = [];
        foreach ($petIds as $petId) {
            $created[] = Reminder::create([
                'household_id' => $householdId,
                'pet_id' => $petId,
                'reminder_group_id' => $groupId,
                'activity_type_id' => $validated['activity_type_id'],
                'title' => $validated['title'],
                'time' => $validated['time'],
                'frequency' => $validated['frequency'],
                'is_active' => $validated['is_active'] ?? true,
            ]);
        }

        $ids = collect($created)->pluck('id');

        return response()->json(
            Reminder::with('activityType')->whereIn('id', $ids)->get(),
            201
        );
    }
}
