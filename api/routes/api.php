<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ActivityBulkController;
use App\Http\Controllers\FeederWebhookController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::get('/invites/{token}', [\App\Http\Controllers\HouseholdInviteController::class, 'show']);

Route::post('/webhooks/feeder/{token}', [FeederWebhookController::class, 'store'])
    ->middleware('throttle:60,1');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load('households');
    });

    Route::apiResource('households', HouseholdController::class);
    Route::post('households/{household}/invite', [HouseholdController::class, 'invite']);
    Route::post('invites/{token}/accept', [\App\Http\Controllers\HouseholdInviteController::class, 'accept']);

    // Push Subscriptions
    Route::post('user/push-subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'store']);
    Route::delete('user/push-subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy']);

    // Nested resources
    Route::apiResource('households.pets', \App\Http\Controllers\PetController::class);
    // Multipart-Uploads (Profilbild) sind per POST zuverlässiger als PUT (PHP/Limits).
    Route::post('households/{household}/pets/{pet}', [\App\Http\Controllers\PetController::class, 'update']);
    Route::apiResource('households.feeding-plans', \App\Http\Controllers\FeedingPlanController::class);
    Route::get('households/{household}/pets/{pet}/feeding-week', [\App\Http\Controllers\PetFeedingWeekController::class, 'show']);
    Route::apiResource('households.activity-types', \App\Http\Controllers\ActivityTypeController::class);

    Route::get('households/{household}/activity-logs', [ActivityLogController::class, 'index']);
    Route::post('households/{household}/activity-logs', [ActivityLogController::class, 'store']);
    Route::post('households/{household}/activity-logs/bulk', [ActivityBulkController::class, 'store']);
    Route::delete('households/{household}/activity-logs/{activityLog}', [ActivityLogController::class, 'destroy']);
    Route::delete('households/{household}/members/{user}', [HouseholdController::class, 'removeMember']);

    Route::get('households/{household}/feeder-config', [\App\Http\Controllers\FeederConfigController::class, 'show']);
    Route::put('households/{household}/feeder-config', [\App\Http\Controllers\FeederConfigController::class, 'update']);
    Route::post('households/{household}/feeder-config/regenerate-token', [\App\Http\Controllers\FeederConfigController::class, 'regenerateToken']);
    Route::get('households/{household}/feeder-events', [\App\Http\Controllers\FeederEventController::class, 'index']);
    Route::get('households/{household}/feeder-events/{feederEvent}', [\App\Http\Controllers\FeederEventController::class, 'show']);
});
