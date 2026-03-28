<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedingPlanSlot extends Model
{
    protected $fillable = [
        'feeding_plan_id', 'activity_type_id', 'title', 'time', 'weekdays', 'is_active',
    ];

    protected $casts = [
        'weekdays' => 'array',
        'is_active' => 'boolean',
    ];

    public function feedingPlan(): BelongsTo
    {
        return $this->belongsTo(FeedingPlan::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'feeding_plan_slot_id');
    }
}
