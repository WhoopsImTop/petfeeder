<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pet;
use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;

class PetController extends Controller
{
    public function index(Request $request, string $householdId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        return response()->json($household->pets);
    }

    public function store(StorePetRequest $request, string $householdId)
    {
        $validated = $request->validated();
        $validated['household_id'] = $householdId;
        
        $pet = Pet::create($validated);
        
        return response()->json($pet, 201);
    }

    public function show(Request $request, string $householdId, string $petId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $pet = $household->pets()->findOrFail($petId);
        
        return response()->json($pet);
    }

    public function update(UpdatePetRequest $request, string $householdId, string $petId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $pet = $household->pets()->findOrFail($petId);
        
        $pet->update($request->validated());
        
        return response()->json($pet);
    }

    public function destroy(Request $request, string $householdId, string $petId)
    {
        $household = $request->user()->households()->findOrFail($householdId);
        $pet = $household->pets()->findOrFail($petId);
        
        $pet->delete();
        
        return response()->json(null, 204);
    }
}
