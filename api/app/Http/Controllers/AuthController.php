<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Household;
use App\Models\User;
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
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $household = Household::create(['name' => 'Mein Haushalt']);
        $household->users()->attach($user->id, ['role' => 'admin']);

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
