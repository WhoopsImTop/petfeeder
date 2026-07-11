<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthTokenService
{
    public function accessTokenExpiresAt(): \DateTimeInterface
    {
        $minutes = max(1, (int) config('sanctum.access_token_ttl', 60));

        return now()->addMinutes($minutes);
    }

    public function refreshTokenExpiresAt(): \DateTimeInterface
    {
        $days = max(1, (int) config('sanctum.refresh_token_ttl_days', 30));

        return now()->addDays($days);
    }

    public function accessTokenExpiresInSeconds(): int
    {
        return max(1, (int) config('sanctum.access_token_ttl', 60)) * 60;
    }

    /**
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string}
     */
    public function issueTokenPair(User $user): array
    {
        $access = $user->createToken('access', ['access'], $this->accessTokenExpiresAt());
        $refresh = $user->createToken('refresh', ['refresh'], $this->refreshTokenExpiresAt());

        return [
            'access_token' => $access->plainTextToken,
            'refresh_token' => $refresh->plainTextToken,
            'expires_in' => $this->accessTokenExpiresInSeconds(),
            'token_type' => 'Bearer',
        ];
    }

    /**
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string}
     */
    public function refreshTokenPair(string $refreshTokenPlain): array
    {
        $token = PersonalAccessToken::findToken($refreshTokenPlain);

        if (! $token || $token->name !== 'refresh' || ! $token->can('refresh')) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Der Refresh-Token ist ungültig.'],
            ]);
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();

            throw ValidationException::withMessages([
                'refresh_token' => ['Der Refresh-Token ist abgelaufen.'],
            ]);
        }

        /** @var User $user */
        $user = $token->tokenable;

        $token->delete();

        $user->tokens()->where('name', 'access')->delete();

        return $this->issueTokenPair($user);
    }

    public function revokeCurrentAccessToken(User $user): void
    {
        $current = $user->currentAccessToken();

        if ($current) {
            $current->delete();
        }
    }

    public function revokeRefreshToken(User $user, ?string $refreshTokenPlain): void
    {
        if (! $refreshTokenPlain) {
            return;
        }

        $token = PersonalAccessToken::findToken($refreshTokenPlain);

        if ($token && $token->tokenable_id === $user->id && $token->tokenable_type === $user->getMorphClass()) {
            $token->delete();
        }
    }
}
