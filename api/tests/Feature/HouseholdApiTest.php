<?php

use App\Mail\HouseholdMemberAddedMail;
use App\Models\Household;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('unauthenticated users cannot access households', function () {
    $response = $this->getJson('/api/households');
    $response->assertStatus(401);
});

test('user can create a household and is attached as admin', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/households', [
        'name' => 'My new Home'
    ]);

    $response->assertStatus(201);
    expect($user->households()->where('name', 'My new Home')->first()->pivot->role)->toBe('admin');
});

test('tenant isolation: user cannot see households they do not belong to', function () {
    $userA = User::factory()->create();
    $householdA = Household::factory()->create(['name' => 'Household A']);
    $householdA->users()->attach($userA->id, ['role' => 'admin']);

    $userB = User::factory()->create();
    $householdB = Household::factory()->create(['name' => 'Household B']);
    $householdB->users()->attach($userB->id, ['role' => 'admin']);

    $response = $this->actingAs($userA)->getJson('/api/households/' . $householdB->id);
    
    // Authorization check in controller fails via findOrFail => 404
    $response->assertStatus(404);
});

test('authorization: only admins can invite users to a household', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $strangerToInvite = User::factory()->create();

    $household = Household::factory()->create();
    $household->users()->attach($admin->id, ['role' => 'admin']);
    $household->users()->attach($member->id, ['role' => 'member']);

    Mail::fake();

    // Admin invites => success
    $responseAdmin = $this->actingAs($admin)->postJson('/api/households/' . $household->id . '/invite', [
        'email' => $strangerToInvite->email,
        'role' => 'member'
    ]);
    $responseAdmin->assertStatus(200);

    Mail::assertSent(HouseholdMemberAddedMail::class);

    $strangerToInvite2 = User::factory()->create();
    
    // Member invites => fails
    $responseMember = $this->actingAs($member)->postJson('/api/households/' . $household->id . '/invite', [
        'email' => $strangerToInvite2->email,
        'role' => 'member'
    ]);
    
    // Fails via findOrFail on admin role pivot check => 404
    $responseMember->assertStatus(404);
});

test('authorization: only admins can rename a household', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $household = Household::factory()->create(['name' => 'Old Name']);

    $household->users()->attach($admin->id, ['role' => 'admin']);
    $household->users()->attach($member->id, ['role' => 'member']);

    $adminResponse = $this->actingAs($admin)->putJson('/api/households/' . $household->id, [
        'name' => 'New Admin Name',
    ]);

    $adminResponse->assertStatus(200)
        ->assertJsonPath('name', 'New Admin Name');

    $memberResponse = $this->actingAs($member)->putJson('/api/households/' . $household->id, [
        'name' => 'Member Should Not Rename',
    ]);

    $memberResponse->assertStatus(404);
    expect($household->fresh()->name)->toBe('New Admin Name');
});

test('authorization: only admins can remove household members', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $target = User::factory()->create();
    $household = Household::factory()->create();

    $household->users()->attach($admin->id, ['role' => 'admin']);
    $household->users()->attach($member->id, ['role' => 'member']);
    $household->users()->attach($target->id, ['role' => 'member']);

    $memberResponse = $this->actingAs($member)->deleteJson('/api/households/' . $household->id . '/members/' . $target->id);
    $memberResponse->assertStatus(404);
    expect($household->users()->where('users.id', $target->id)->exists())->toBeTrue();

    $adminResponse = $this->actingAs($admin)->deleteJson('/api/households/' . $household->id . '/members/' . $target->id);
    $adminResponse->assertStatus(200);
    expect($household->fresh()->users()->where('users.id', $target->id)->exists())->toBeFalse();
});
