<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityType;

class ActivityTypeController extends Controller
{
    public function index(Request $request, string $householdId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        return response()->json($household->activityTypes);
    }

    public function store(Request $request, string $householdId)
    {
        $household = $request->user()->households()->findOrFail($householdId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:boolean,value,timer',
            'icon' => 'nullable|string|max:255',
            'is_fast_action' => 'boolean',
        ]);

        $activityType = $household->activityTypes()->create($validated);

        return response()->json($activityType, 201);
    }

    public function update(Request $request, string $householdId, string $id)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $activityType = $household->activityTypes()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:boolean,value,timer',
            'icon' => 'nullable|string|max:255',
            'is_fast_action' => 'boolean',
        ]);

        $activityType->update($validated);

        return response()->json($activityType);
    }

    public function destroy(Request $request, string $householdId, string $id)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $activityType = $household->activityTypes()->findOrFail($id);
        
        $activityType->delete();

        return response()->json(null, 204);
    }
}
