<?php

namespace App\Http\Controllers;

use App\Mail\HouseholdInvitationPendingMail;
use App\Mail\HouseholdMemberAddedMail;
use App\Models\Household;
use App\Models\HouseholdInvite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
     * Einladung per E-Mail: bestehende Nutzer werden direkt hinzugefügt und benachrichtigt;
     * unbekannte Adressen erhalten eine Einladung mit Registrierungslink.
     */
    public function invite(Request $request, string $id)
    {
        try {
        $household = $request->user()->households()->wherePivot('role', 'admin')->findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'sometimes|string|in:admin,member,sitter',
            'expires_at' => 'nullable|date',
        ]);

        $rawExpiresAt = $validated['expires_at'] ?? null;
        $expiresAt = ($rawExpiresAt === null || $rawExpiresAt === '') ? null : $rawExpiresAt;

        $emailNormalized = strtolower(trim($validated['email']));
        $userToInvite = User::whereRaw('LOWER(email) = ?', [$emailNormalized])->first();

        if ($userToInvite) {
            $existingMembership = $household->users()->where('users.id', $userToInvite->id)->first();

            if ($existingMembership) {
                $existingExpiresAt = $existingMembership->pivot->expires_at;

                if ($existingExpiresAt && Carbon::parse($existingExpiresAt)->isPast()) {
                    $household->users()->updateExistingPivot($userToInvite->id, [
                        'role' => $validated['role'] ?? 'member',
                        'expires_at' => $expiresAt,
                    ]);

                    try {
                        Mail::to($userToInvite->email)->send(
                            new HouseholdMemberAddedMail($household, $request->user(), $userToInvite)
                        );
                    } catch (\Throwable $e) {
                        Log::warning('household_member_added_mail_failed', ['error' => $e->getMessage()]);
                    }

                    return response()->json(['message' => 'Einladung aktualisiert und E-Mail gesendet.']);
                }

                return response()->json(['message' => 'User is already a member.'], 400);
            }

            $household->users()->attach($userToInvite->id, [
                'role' => $validated['role'] ?? 'member',
                'expires_at' => $expiresAt,
            ]);

            try {
                Mail::to($userToInvite->email)->send(
                    new HouseholdMemberAddedMail($household, $request->user(), $userToInvite)
                );
            } catch (\Throwable $e) {
                Log::warning('household_member_added_mail_failed', ['error' => $e->getMessage()]);
            }

            return response()->json(['message' => 'User successfully invited.']);
        }

        $existingPending = HouseholdInvite::where('household_id', $household->id)
            ->whereRaw('LOWER(email) = ?', [$emailNormalized])
            ->whereNull('accepted_at')
            ->first();

        if ($existingPending) {
            $existingPending->update([
                'token' => Str::random(64),
                'role' => $validated['role'] ?? 'member',
                'expires_at' => $expiresAt,
                'invited_by_user_id' => $request->user()->id,
            ]);
            $existingPending->refresh();

            try {
                Mail::to($existingPending->email)->send(
                    new HouseholdInvitationPendingMail($existingPending, $household, $request->user())
                );
            } catch (\Throwable $e) {
                Log::warning('household_invite_mail_failed', ['error' => $e->getMessage()]);
            }

            return response()->json(['message' => 'Einladung erneut gesendet.']);
        }

        $invite = HouseholdInvite::create([
            'household_id' => $household->id,
            'email' => $emailNormalized,
            'role' => $validated['role'] ?? 'member',
            'expires_at' => $expiresAt,
            'token' => Str::random(64),
            'invited_by_user_id' => $request->user()->id,
        ]);

        try {
            Mail::to($invite->email)->send(
                new HouseholdInvitationPendingMail($invite, $household, $request->user())
            );
        } catch (\Throwable $e) {
            Log::warning('household_invite_mail_failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'Einladung per E-Mail gesendet.']);
    } catch (\Throwable $e) {
        Log::warning('household_invite_mail_failed', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Einladung per E-Mail fehlgeschlagen.'], 500);
    }
    }

    /**
     * Remove a member from the household (admin only).
     */
    public function removeMember(Request $request, string $id, string $userId)
    {
        $household = $request->user()->households()->wherePivot('role', 'admin')->findOrFail($id);

        if ((int) $request->user()->id === (int) $userId) {
            return response()->json(['message' => 'Du kannst dich nicht selbst entfernen.'], 422);
        }

        $isMember = $household->users()->where('users.id', $userId)->exists();
        if (!$isMember) {
            return response()->json(['message' => 'Mitglied nicht gefunden.'], 404);
        }

        $household->users()->detach($userId);

        return response()->json(['message' => 'Mitglied entfernt.']);
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
