<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ActivityLog;
use App\Http\Requests\StoreActivityLogRequest;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs for the household.
     */
    public function index(Request $request, string $householdId)
    {
        // Authorize user for this household
        $household = $request->user()->households()->findOrFail($householdId);

        $query = ActivityLog::with(['pet', 'activityType', 'user'])
            ->whereHas('pet', function ($q) use ($householdId) {
                $q->where('household_id', $householdId);
            });

        if ($request->has('pet_id')) {
            $query->where('pet_id', $request->pet_id);
        }

        if ($request->has('activity_type_id')) {
            $query->where('activity_type_id', $request->activity_type_id);
        }

        return response()->json($query->latest('started_at')->get());
    }

    /**
     * Store a newly created activity log.
     */
    public function store(StoreActivityLogRequest $request, string $householdId)
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;

        $activityLog = ActivityLog::create($validated);

        // Load the pet and activity type
        $activityLog->load(['pet', 'activityType', 'user']);

        // Send push notification to other household members
        $household = $request->user()->households()->findOrFail($householdId);
        $members = $household->users()->where('users.id', '!=', $request->user()->id)->get();
        
        if ($members->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($members, new \App\Notifications\ActivityLoggedNotification($activityLog));
        }

        return response()->json($activityLog, 201);
    }

    /**
     * Remove the specified activity log.
     */
    public function destroy(Request $request, string $householdId, ActivityLog $activityLog)
    {
        // First confirm household matches
        $household = $request->user()->households()->findOrFail($householdId);
        
        // Ensure log belongs to a pet in this household
        if ($activityLog->pet->household_id != $household->id) {
            abort(403);
        }

        $activityLog->delete();

        return response()->json(null, 204);
    }
}
