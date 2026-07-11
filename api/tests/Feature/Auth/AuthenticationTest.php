<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['access_token', 'refresh_token', 'expires_in', 'token_type', 'user']);

    expect(PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(2);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
});

test('users can logout', function () {
    $user = User::factory()->create();
    $tokens = app(\App\Services\AuthTokenService::class)->issueTokenPair($user);

    $response = $this->withToken($tokens['access_token'])
        ->postJson('/api/logout', [
            'refresh_token' => $tokens['refresh_token'],
        ]);

    $response->assertOk();
    expect(PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(0);
});

test('refresh token returns a new token pair', function () {
    $user = User::factory()->create();
    $tokens = app(\App\Services\AuthTokenService::class)->issueTokenPair($user);

    $response = $this->postJson('/api/refresh', [
        'refresh_token' => $tokens['refresh_token'],
    ]);

    $response->assertOk()
        ->assertJsonStructure(['access_token', 'refresh_token', 'expires_in', 'token_type']);

    expect($response->json('access_token'))->not->toBe($tokens['access_token']);
});

test('invalid refresh token is rejected', function () {
    $response = $this->postJson('/api/refresh', [
        'refresh_token' => 'invalid-token',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['refresh_token']);
});
