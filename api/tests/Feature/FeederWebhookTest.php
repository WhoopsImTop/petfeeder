<?php

use App\Models\ActivityLog;
use App\Models\ActivityType;
use App\Models\FeederEvent;
use App\Models\Household;
use App\Models\User;
use App\Notifications\FeederPreyDetectedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

function setupFeederHousehold(): array
{
    $user = User::factory()->create();
    $household = Household::factory()->create([
        'feeder_webhook_token' => Str::random(64),
        'feeder_webhook_enabled' => true,
    ]);
    $household->users()->attach($user->id, ['role' => 'admin']);

    $openType = ActivityType::factory()->create(['household_id' => $household->id, 'name' => 'Open']);
    $stayType = ActivityType::factory()->create(['household_id' => $household->id, 'name' => 'Stay']);
    $noneType = ActivityType::factory()->create(['household_id' => $household->id, 'name' => 'None']);

    $household->update([
        'feeder_action_open_activity_type_id' => $openType->id,
        'feeder_action_stay_closed_activity_type_id' => $stayType->id,
        'feeder_action_none_activity_type_id' => $noneType->id,
    ]);

    return [$user, $household->fresh(), $openType, $stayType, $noneType];
}

test('webhook accepts valid multipart payload and creates feeder event', function () {
    [, $household] = setupFeederHousehold();

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->post('/api/webhooks/feeder/'.$household->feeder_webhook_token, [
        'timestamp' => now()->toIso8601String(),
        'label' => 'cat_only',
        'action' => 'open',
        'confidence' => '0.92',
        'mouth_status' => 'none',
        'detections' => json_encode(['cat' => 1]),
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('feeder_events', [
        'household_id' => $household->id,
        'label' => 'cat_only',
        'action' => 'open',
    ]);
    $this->assertDatabaseHas('activity_logs', [
        'household_id' => $household->id,
        'pet_id' => null,
        'user_id' => null,
    ]);
});

test('webhook sends push notification only for stay_closed', function () {
    Notification::fake();
    [$user, $household] = setupFeederHousehold();
    $member = User::factory()->create();
    $household->users()->attach($member->id, ['role' => 'member']);

    $this->withHeaders(['Accept' => 'application/json'])
        ->post('/api/webhooks/feeder/'.$household->feeder_webhook_token, [
        'timestamp' => now()->toIso8601String(),
        'label' => 'cat_only',
        'action' => 'open',
        'confidence' => '0.5',
        'detections' => json_encode([]),
    ])->assertStatus(201);

    Notification::assertNothingSent();

    $this->withHeaders(['Accept' => 'application/json'])
        ->post('/api/webhooks/feeder/'.$household->feeder_webhook_token, [
        'timestamp' => now()->toIso8601String(),
        'label' => 'prey_detected',
        'action' => 'stay_closed',
        'confidence' => '0.88',
        'detections' => json_encode(['prey' => true]),
    ])->assertStatus(201);

    Notification::assertSentTo(
        [$user, $member],
        FeederPreyDetectedNotification::class,
    );
});

test('webhook rejects invalid token', function () {
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->post('/api/webhooks/feeder/invalid-token', [
        'timestamp' => now()->toIso8601String(),
        'label' => 'cat_only',
        'action' => 'open',
        'confidence' => '0.5',
        'detections' => json_encode([]),
    ]);

    $response->assertStatus(404);
});

test('webhook accepts optional jpeg image', function () {
    [, $household] = setupFeederHousehold();

    $image = UploadedFile::fake()->create('detection.jpg', 100, 'image/jpeg');

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->post('/api/webhooks/feeder/'.$household->feeder_webhook_token, [
        'timestamp' => now()->toIso8601String(),
        'label' => 'cat_only',
        'action' => 'none',
        'confidence' => '0.7',
        'detections' => json_encode([]),
        'image' => $image,
    ]);

    $response->assertStatus(201);
    $event = FeederEvent::first();
    expect($event->image_path)->not->toBeNull();
});

test('admin can fetch and update feeder config', function () {
    [$user, $household, $openType, $stayType, $noneType] = setupFeederHousehold();

    $this->actingAs($user)
        ->getJson('/api/households/'.$household->id.'/feeder-config')
        ->assertStatus(200)
        ->assertJsonPath('feeder_webhook_enabled', true)
        ->assertJsonStructure(['webhook_url']);

    $this->actingAs($user)
        ->putJson('/api/households/'.$household->id.'/feeder-config', [
            'feeder_webhook_enabled' => false,
        ])
        ->assertStatus(200)
        ->assertJsonPath('feeder_webhook_enabled', false);
});

test('member cannot update feeder config', function () {
    [, $household] = setupFeederHousehold();
    $member = User::factory()->create();
    $household->users()->attach($member->id, ['role' => 'member']);

    $this->actingAs($member)
        ->putJson('/api/households/'.$household->id.'/feeder-config', [
            'feeder_webhook_enabled' => false,
        ])
        ->assertStatus(404);
});

test('admin can regenerate webhook token', function () {
    [$user, $household] = setupFeederHousehold();
    $oldToken = $household->feeder_webhook_token;

    $this->actingAs($user)
        ->postJson('/api/households/'.$household->id.'/feeder-config/regenerate-token')
        ->assertStatus(200)
        ->assertJsonStructure(['webhook_url']);

    expect($household->fresh()->feeder_webhook_token)->not->toBe($oldToken);
});

test('household members can list feeder events', function () {
    [$user, $household] = setupFeederHousehold();

    FeederEvent::create([
        'household_id' => $household->id,
        'detected_at' => now(),
        'label' => 'cat_only',
        'action' => 'open',
        'confidence' => 0.9,
        'detections' => ['cat' => 1],
    ]);

    $this->actingAs($user)
        ->getJson('/api/households/'.$household->id.'/feeder-events')
        ->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonPath('0.label', 'cat_only');
});

test('user can create activity log with past started_at', function () {
    $user = User::factory()->create();
    $household = Household::factory()->create();
    $household->users()->attach($user->id, ['role' => 'member']);

    $pet = \App\Models\Pet::factory()->create(['household_id' => $household->id]);
    $type = ActivityType::factory()->create(['household_id' => $household->id]);

    $past = now()->subDays(3)->format('Y-m-d H:i:s');

    $response = $this->actingAs($user)->postJson('/api/households/'.$household->id.'/activity-logs', [
        'pet_id' => $pet->id,
        'activity_type_id' => $type->id,
        'started_at' => $past,
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('activity_logs', [
        'pet_id' => $pet->id,
        'started_at' => $past,
    ]);
});

test('activity log index includes household scoped logs without pet', function () {
    [$user, $household, $openType] = setupFeederHousehold();

    ActivityLog::create([
        'household_id' => $household->id,
        'pet_id' => null,
        'activity_type_id' => $openType->id,
        'user_id' => null,
        'started_at' => now(),
    ]);

    $this->actingAs($user)
        ->getJson('/api/households/'.$household->id.'/activity-logs')
        ->assertStatus(200)
        ->assertJsonCount(1);
});
