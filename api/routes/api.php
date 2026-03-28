<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ActivityBulkController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load('households');
    });

    Route::apiResource('households', HouseholdController::class);
    Route::post('households/{household}/invite', [HouseholdController::class, 'invite']);

    // Push Subscriptions
    Route::post('user/push-subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'store']);

    // Nested resources
    Route::apiResource('households.pets', \App\Http\Controllers\PetController::class);
    Route::apiResource('households.feeding-plans', \App\Http\Controllers\FeedingPlanController::class);
    Route::get('households/{household}/pets/{pet}/feeding-week', [\App\Http\Controllers\PetFeedingWeekController::class, 'show']);
    Route::apiResource('households.activity-types', \App\Http\Controllers\ActivityTypeController::class);

    Route::get('households/{household}/activity-logs', [ActivityLogController::class, 'index']);
    Route::post('households/{household}/activity-logs', [ActivityLogController::class, 'store']);
    Route::post('households/{household}/activity-logs/bulk', [ActivityBulkController::class, 'store']);
    Route::delete('households/{household}/activity-logs/{activityLog}', [ActivityLogController::class, 'destroy']);
});
