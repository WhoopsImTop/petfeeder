<?php

use App\Models\ActivityType;
use App\Models\Household;
use App\Models\Pet;
use App\Models\Reminder;
use App\Models\User;

test('user can create bulk reminders for multiple pets with shared group id', function () {
    $user = User::factory()->create();
    $household = Household::factory()->create();
    $household->users()->attach($user->id, ['role' => 'member']);

    $pet1 = Pet::factory()->create(['household_id' => $household->id]);
    $pet2 = Pet::factory()->create(['household_id' => $household->id]);
    $type = ActivityType::factory()->create(['household_id' => $household->id]);

    $response = $this->actingAs($user)->postJson('/api/households/'.$household->id.'/reminders/bulk', [
        'pet_ids' => [$pet1->id, $pet2->id],
        'activity_type_id' => $type->id,
        'title' => 'Morgenfutter',
        'time' => '08:00',
        'frequency' => 'daily',
        'is_active' => true,
    ]);

    $response->assertStatus(201);
    $response->assertJsonCount(2);

    expect(Reminder::count())->toBe(2);
    expect(Reminder::pluck('reminder_group_id')->unique()->filter()->values())->toHaveCount(1);
});

test('bulk reminders requires at least two pets', function () {
    $user = User::factory()->create();
    $household = Household::factory()->create();
    $household->users()->attach($user->id, ['role' => 'member']);

    $pet = Pet::factory()->create(['household_id' => $household->id]);
    $type = ActivityType::factory()->create(['household_id' => $household->id]);

    $response = $this->actingAs($user)->postJson('/api/households/'.$household->id.'/reminders/bulk', [
        'pet_ids' => [$pet->id],
        'activity_type_id' => $type->id,
        'title' => 'Morgenfutter',
        'time' => '08:00',
        'frequency' => 'daily',
    ]);

    $response->assertStatus(422);
});

test('user can delete all reminders in a group', function () {
    $user = User::factory()->create();
    $household = Household::factory()->create();
    $household->users()->attach($user->id, ['role' => 'member']);

    $pet1 = Pet::factory()->create(['household_id' => $household->id]);
    $pet2 = Pet::factory()->create(['household_id' => $household->id]);
    $type = ActivityType::factory()->create(['household_id' => $household->id]);

    $this->actingAs($user)->postJson('/api/households/'.$household->id.'/reminders/bulk', [
        'pet_ids' => [$pet1->id, $pet2->id],
        'activity_type_id' => $type->id,
        'title' => 'Abend',
        'time' => '18:00',
        'frequency' => 'daily',
    ]);

    $reminder = Reminder::where('pet_id', $pet1->id)->firstOrFail();

    $response = $this->actingAs($user)->deleteJson(
        '/api/households/'.$household->id.'/pets/'.$pet1->id.'/reminders/'.$reminder->id.'?scope=group'
    );

    $response->assertStatus(204);
    expect(Reminder::count())->toBe(0);
});
