<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdInvite;
use App\Models\User;
use App\Services\AuthTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthTokenService $tokenService,
    ) {}

    /**
     * Register a new user and return a token pair.
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
            ...$this->tokenService->issueTokenPair($user),
            'user' => $user->load('households'),
        ], 201);
    }

    /**
     * Authenticate user and return a token pair.
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
            ...$this->tokenService->issueTokenPair($user),
            'user' => $user->load('households'),
        ]);
    }

    /**
     * Issue a new access/refresh token pair using a valid refresh token.
     */
    public function refresh(Request $request)
    {
        $validated = $request->validate([
            'refresh_token' => 'required|string',
        ]);

        return response()->json(
            $this->tokenService->refreshTokenPair($validated['refresh_token'])
        );
    }

    /**
     * Revoke the current access token and optional refresh token.
     */
    public function logout(Request $request)
    {
        $validated = $request->validate([
            'refresh_token' => 'nullable|string',
        ]);

        $this->tokenService->revokeCurrentAccessToken($request->user());
        $this->tokenService->revokeRefreshToken($request->user(), $validated['refresh_token'] ?? null);

        return response()->json(['message' => 'Erfolgreich abgemeldet.']);
    }
}
