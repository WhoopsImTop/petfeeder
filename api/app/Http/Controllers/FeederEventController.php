<?php

namespace App\Http\Controllers;

use App\Models\FeederEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeederEventController extends Controller
{
    public function index(Request $request, string $householdId): JsonResponse
    {
        $household = $request->user()->households()->findOrFail($householdId);

        $query = FeederEvent::query()
            ->where('household_id', $household->id)
            ->with('activityLog.activityType')
            ->orderByDesc('detected_at');

        if ($request->filled('from')) {
            $query->where('detected_at', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->where('detected_at', '<=', $request->query('to'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        return response()->json($query->get());
    }

    public function show(Request $request, string $householdId, FeederEvent $feederEvent): JsonResponse
    {
        $household = $request->user()->households()->findOrFail($householdId);

        if ($feederEvent->household_id !== (int) $household->id) {
            abort(404);
        }

        return response()->json($feederEvent->load('activityLog.activityType'));
    }
}
