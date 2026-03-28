<?php

use App\Models\Household;
use App\Models\User;
use App\Models\Pet;
use App\Models\ActivityType;
use App\Models\ActivityLog;

test('user can fetch activity logs for a household they belong to', function () {
    $user = User::factory()->create();
    $household = Household::factory()->create();
    $household->users()->attach($user->id, ['role' => 'member']);

    $pet = Pet::factory()->create(['household_id' => $household->id]);
    $type = ActivityType::factory()->create(['household_id' => $household->id, 'is_fast_action' => true]);

    ActivityLog::factory()->count(3)->create([
        'pet_id' => $pet->id,
        'activity_type_id' => $type->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->getJson('/api/households/' . $household->id . '/activity-logs');
    
    $response->assertStatus(200);
    $response->assertJsonCount(3);
});

test('tenant isolation: user cannot create an activity log for a pet from another household', function () {
    $user = User::factory()->create();
    $householdA = Household::factory()->create();
    $householdA->users()->attach($user->id, ['role' => 'member']); // User is in A
    $petA = Pet::factory()->create(['household_id' => $householdA->id]);
    $typeA = ActivityType::factory()->create(['household_id' => $householdA->id]);

    $householdB = Household::factory()->create();
    $petB = Pet::factory()->create(['household_id' => $householdB->id]); // Belong to B
    
    // User tries to log activity for petB in householdA endpoint
    $response = $this->actingAs($user)->postJson('/api/households/' . $householdA->id . '/activity-logs', [
        'pet_id' => $petB->id,
        'activity_type_id' => $typeA->id,
        'value' => '5 km',
        'started_at' => now()->toIso8601String(),
    ]);

    // Should fail validation because pet_id must belong to householdA
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['pet_id']);
});

test('tenant isolation: user cannot read activity logs of a household they are not in', function () {
    $user = User::factory()->create();
    $householdB = Household::factory()->create();
    
    $response = $this->actingAs($user)->getJson('/api/households/' . $householdB->id . '/activity-logs');

    // Should fail authorization when fetching household in ActivityLogController => 404
    $response->assertStatus(404);
});

test('user can perform a fast action activity log', function () {
    $user = User::factory()->create();
    $household = Household::factory()->create();
    $household->users()->attach($user->id, ['role' => 'member']);

    $pet = Pet::factory()->create(['household_id' => $household->id]);
    $type = ActivityType::factory()->create(['household_id' => $household->id, 'is_fast_action' => true]);

    $response = $this->actingAs($user)->postJson('/api/households/' . $household->id . '/activity-logs', [
        'pet_id' => $pet->id,
        'activity_type_id' => $type->id,
        'started_at' => now()->toIso8601String(), // Fast actions don't need value/ended_at
    ]);

    $response->assertStatus(201);
    
    $this->assertDatabaseHas('activity_logs', [
        'pet_id' => $pet->id,
        'activity_type_id' => $type->id,
        'user_id' => $user->id,
    ]);
});
