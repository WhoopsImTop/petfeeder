<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use App\Http\Requests\StoreReminderRequest;
use App\Http\Requests\UpdateReminderRequest;

class ReminderController extends Controller
{
    public function index(Request $request, string $householdId, string $petId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $pet = $household->pets()->findOrFail($petId);
        
        return response()->json($pet->reminders()->with('activityType')->get());
    }

    public function store(StoreReminderRequest $request, string $householdId, string $petId)
    {
        $validated = $request->validated();
        $validated['household_id'] = $householdId;
        $validated['pet_id'] = $petId;
        
        $reminder = Reminder::create($validated);
        
        return response()->json($reminder, 201);
    }

    public function show(Request $request, string $householdId, string $petId, string $reminderId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $pet = $household->pets()->findOrFail($petId);
        $reminder = $pet->reminders()->findOrFail($reminderId);
        
        return response()->json($reminder);
    }

    public function update(UpdateReminderRequest $request, string $householdId, string $petId, string $reminderId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $pet = $household->pets()->findOrFail($petId);
        $reminder = $pet->reminders()->findOrFail($reminderId);
        
        $reminder->update($request->validated());
        
        return response()->json($reminder);
    }

    public function destroy(Request $request, string $householdId, string $petId, string $reminderId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $pet = $household->pets()->findOrFail($petId);
        $reminder = $pet->reminders()->findOrFail($reminderId);

        if ($request->query('scope') === 'group' && $reminder->reminder_group_id) {
            Reminder::where('household_id', $householdId)
                ->where('reminder_group_id', $reminder->reminder_group_id)
                ->delete();
        } else {
            $reminder->delete();
        }

        return response()->json(null, 204);
    }
}
