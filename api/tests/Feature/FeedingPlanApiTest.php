<?php

use App\Models\ActivityType;
use App\Models\FeedingPlan;
use App\Models\Household;
use App\Models\Pet;
use App\Models\User;

test('user can create feeding plan with multiple pets and slots', function () {
    $user = User::factory()->create();
    $household = Household::factory()->create();
    $household->users()->attach($user->id, ['role' => 'member']);

    $pet1 = Pet::factory()->create(['household_id' => $household->id]);
    $pet2 = Pet::factory()->create(['household_id' => $household->id]);
    $type = ActivityType::factory()->create(['household_id' => $household->id]);

    $response = $this->actingAs($user)->postJson('/api/households/'.$household->id.'/feeding-plans', [
        'name' => 'Katzenplan',
        'pet_ids' => [$pet1->id, $pet2->id],
        'slots' => [[
            'activity_type_id' => $type->id,
            'time' => '08:00',
            'weekdays' => [1, 2, 3, 4, 5, 6, 7],
            'title' => 'Morgens',
        ]],
    ]);

    $response->assertStatus(201);
    expect(FeedingPlan::count())->toBe(1);
    expect($pet1->fresh()->feedingPlans)->toHaveCount(1);
    expect($pet2->fresh()->feedingPlans)->toHaveCount(1);
});

test('user can delete feeding plan', function () {
    $user = User::factory()->create();
    $household = Household::factory()->create();
    $household->users()->attach($user->id, ['role' => 'member']);

    $pet1 = Pet::factory()->create(['household_id' => $household->id]);
    $type = ActivityType::factory()->create(['household_id' => $household->id]);

    $create = $this->actingAs($user)->postJson('/api/households/'.$household->id.'/feeding-plans', [
        'name' => 'Plan',
        'pet_ids' => [$pet1->id],
        'slots' => [[
            'activity_type_id' => $type->id,
            'time' => '18:00',
            'weekdays' => [1, 2, 3, 4, 5, 6, 7],
        ]],
    ]);

    $create->assertStatus(201);
    $planId = $create->json('id');

    $response = $this->actingAs($user)->deleteJson('/api/households/'.$household->id.'/feeding-plans/'.$planId);

    $response->assertStatus(204);
    expect(FeedingPlan::count())->toBe(0);
});
