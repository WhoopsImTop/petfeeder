<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdInvite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user and return a token.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'invite_token' => 'nullable|string|size:64',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $invite = null;
            if (! empty($validated['invite_token'])) {
                $invite = HouseholdInvite::where('token', $validated['invite_token'])
                    ->whereNull('accepted_at')
                    ->lockForUpdate()
                    ->first();

                if (! $invite) {
                    throw ValidationException::withMessages([
                        'invite_token' => ['Die Einladung ist ungültig oder wurde bereits verwendet.'],
                    ]);
                }

                if ($invite->isExpired()) {
                    throw ValidationException::withMessages([
                        'invite_token' => ['Diese Einladung ist abgelaufen.'],
                    ]);
                }

                if (strcasecmp($invite->email, $validated['email']) !== 0) {
                    throw ValidationException::withMessages([
                        'email' => ['Die E-Mail-Adresse muss mit der Einladung übereinstimmen.'],
                    ]);
                }
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if ($invite) {
                $invite->household->users()->attach($user->id, [
                    'role' => $invite->role,
                    'expires_at' => $invite->expires_at,
                ]);
                $invite->update(['accepted_at' => now()]);
            } else {
                $household = Household::create(['name' => 'Mein Haushalt']);
                $household->users()->attach($user->id, ['role' => 'admin']);
            }

            return $user;
        });

        return response()->json([
            'access_token' => $user->createToken('api_token')->plainTextToken,
            'user' => $user->load('households'),
        ], 201);
    }

    /**
     * Authenticate user and return a token.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Die Zugangsdaten sind inkorrekt.'],
            ]);
        }

        return response()->json([
            'access_token' => $user->createToken('api_token')->plainTextToken,
            'user' => $user,
        ]);
    }

    /**
     * Revoke the current token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Erfolgreich abgemeldet.']);
    }
}
