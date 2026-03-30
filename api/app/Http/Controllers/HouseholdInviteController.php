<?php

namespace App\Http\Controllers;

use App\Models\HouseholdInvite;
use Illuminate\Http\Request;

class HouseholdInviteController extends Controller
{
    /**
     * Öffentliche Infos zur Einladung (für Registrierungsseite, ohne Auth).
     */
    public function show(string $token)
    {
        $invite = HouseholdInvite::where('token', $token)
            ->whereNull('accepted_at')
            ->with('household')
            ->firstOrFail();

        if ($invite->isExpired()) {
            return response()->json(['message' => 'Diese Einladung ist abgelaufen.'], 410);
        }

        return response()->json([
            'household_name' => $invite->household->name,
            'email' => $invite->email,
            'role' => $invite->role,
        ]);
    }

    /**
     * Einladung annehmen, wenn bereits ein Konto mit derselben E-Mail existiert.
     */
    public function accept(Request $request, string $token)
    {
        $invite = HouseholdInvite::where('token', $token)
            ->whereNull('accepted_at')
            ->with('household')
            ->firstOrFail();

        if ($invite->isExpired()) {
            return response()->json(['message' => 'Diese Einladung ist abgelaufen.'], 410);
        }

        if (strcasecmp($invite->email, $request->user()->email) !== 0) {
            return response()->json(['message' => 'Die E-Mail-Adresse passt nicht zu dieser Einladung.'], 403);
        }

        $household = $invite->household;

        if ($household->users()->where('users.id', $request->user()->id)->exists()) {
            $invite->update(['accepted_at' => now()]);

            return response()->json(['message' => 'Du bist bereits Mitglied dieses Haushalts.']);
        }

        $household->users()->attach($request->user()->id, [
            'role' => $invite->role,
            'expires_at' => $invite->expires_at,
        ]);

        $invite->update(['accepted_at' => now()]);

        return response()->json([
            'message' => 'Du bist dem Haushalt beigetreten.',
            'household_id' => $household->id,
        ]);
    }
}
