<?php

use App\Models\User;

test('new users can register', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['access_token', 'refresh_token', 'expires_in', 'user']);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});
