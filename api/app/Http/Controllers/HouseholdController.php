<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Household;
use App\Models\User;

class HouseholdController extends Controller
{
    /**
     * Display a listing of the user's households.
     */
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->households()->with('pets', 'activityTypes')->get()
        );
    }

    /**
     * Store a newly created household.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $household = Household::create($validated);
        
        // Attach user as admin
        $household->users()->attach($request->user()->id, ['role' => 'admin']);

        return response()->json($household->load('pets', 'activityTypes'), 201);
    }

    /**
     * Display the specified household.
     */
    public function show(Request $request, string $id)
    {
        $household = $request->user()
            ->households()
            ->with('pets', 'activityTypes')
            ->with(['users' => function ($query) {
                $query->wherePivotNull('expires_at')
                    ->orWherePivot('expires_at', '>', now());
            }])
            ->findOrFail($id);
        
        return response()->json($household);
    }

    /**
     * Invite a user to the household via email.
     */
    public function invite(Request $request, string $id)
    {
        $household = $request->user()->households()->wherePivot('role', 'admin')->findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'sometimes|string|in:admin,member,sitter',
            'expires_at' => 'nullable|date',
        ]);

        $userToInvite = User::where('email', $validated['email'])->firstOrFail();

        $existingMembership = $household->users()->where('users.id', $userToInvite->id)->first();

        if ($existingMembership) {
            $existingExpiresAt = $existingMembership->pivot->expires_at;

            // If the previous invite/membership expired, allow re-inviting by updating the pivot.
            if ($existingExpiresAt && Carbon::parse($existingExpiresAt)->isPast()) {
                $household->users()->updateExistingPivot($userToInvite->id, [
                    'role' => $validated['role'] ?? 'member',
                    'expires_at' => $validated['expires_at'] ?? null,
                ]);

                return response()->json(['message' => 'User invitation updated.']);
            }

            return response()->json(['message' => 'User is already a member.'], 400);
        }

        $household->users()->attach($userToInvite->id, [
            'role' => $validated['role'] ?? 'member',
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return response()->json(['message' => 'User successfully invited.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $household = $request->user()->households()->wherePivot('role', 'admin')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $household->update($validated);

        return response()->json($household);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $household = $request->user()->households()->wherePivot('role', 'admin')->findOrFail($id);
        $household->delete();

        return response()->json(null, 204);
    }
}
