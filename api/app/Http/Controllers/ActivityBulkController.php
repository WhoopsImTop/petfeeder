<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBulkActivityLogsRequest;
use App\Models\ActivityLog;
use App\Notifications\ActivityLoggedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ActivityBulkController extends Controller
{
    public function store(StoreBulkActivityLogsRequest $request, string $householdId)
    {
        $validated = $request->validated();

        $petIds = array_values(array_unique($validated['pet_ids']));
        $activityTypeId = $validated['activity_type_id'];
        $startedAt = $validated['started_at'];

        $createdIds = [];
        foreach ($petIds as $petId) {
            $activityLog = ActivityLog::create([
                'pet_id' => $petId,
                'activity_type_id' => $activityTypeId,
                'feeding_plan_slot_id' => $validated['feeding_plan_slot_id'] ?? null,
                'user_id' => $request->user()->id,
                'value' => $validated['value'] ?? null,
                'started_at' => $startedAt,
                'ended_at' => $validated['ended_at'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
            $createdIds[] = $activityLog->id;
        }

        $activityLogs = ActivityLog::with(['pet', 'activityType', 'user'])
            ->whereIn('id', $createdIds)
            ->get();

        $first = $activityLogs->first();

        $household = $request->user()->households()->findOrFail($householdId);
        $members = $household->users()->where('users.id', '!=', $request->user()->id)->get();

        if ($first && $members->isNotEmpty()) {
            Notification::send($members, new ActivityLoggedNotification($first, $activityLogs));
        }

        return response()->json($activityLogs, 201);
    }
}

