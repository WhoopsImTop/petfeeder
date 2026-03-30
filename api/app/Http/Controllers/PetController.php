<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
    public function index(Request $request, string $householdId)
    {
        $household = $request->user()->households()->findOrFail($householdId);

        return response()->json(
            $household->pets()->with(['feedingPlans.slots', 'quickActivityTypes'])->get()
        );
    }

    public function store(StorePetRequest $request, string $householdId)
    {
        $validated = $request->validated();
        $validated['household_id'] = $householdId;

        $quickIds = $validated['quick_action_activity_type_ids'] ?? null;
        unset($validated['quick_action_activity_type_ids']);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('pets', 'public');
        }

        $pet = Pet::create($validated);

        if (is_array($quickIds)) {
            $pet->quickActivityTypes()->sync($quickIds);
        }

        return response()->json($pet->load(['feedingPlans.slots', 'quickActivityTypes']), 201);
    }

    public function show(Request $request, string $householdId, string $petId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $pet = $household->pets()->with(['feedingPlans.slots', 'quickActivityTypes'])->findOrFail($petId);

        return response()->json($pet);
    }

    public function update(UpdatePetRequest $request, string $householdId, string $petId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $pet = $household->pets()->findOrFail($petId);

        $data = $request->validated();

        $quickIds = null;
        if (array_key_exists('quick_action_activity_type_ids', $data)) {
            $quickIds = $data['quick_action_activity_type_ids'];
            unset($data['quick_action_activity_type_ids']);
        }

        if ($request->hasFile('avatar')) {
            if ($pet->avatar && ! str_starts_with((string) $pet->avatar, 'http')) {
                Storage::disk('public')->delete($pet->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('pets', 'public');
        }

        $pet->update($data);

        if ($quickIds !== null) {
            $pet->quickActivityTypes()->sync($quickIds);
        }

        return response()->json($pet->fresh()->load(['feedingPlans.slots', 'quickActivityTypes']));
    }

    public function destroy(Request $request, string $householdId, string $petId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $pet = $household->pets()->findOrFail($petId);
        
        $pet->delete();
        
        return response()->json(null, 204);
    }
}
