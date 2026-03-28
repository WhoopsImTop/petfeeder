<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'pet_id', 'activity_type_id', 'feeding_plan_slot_id', 'user_id', 'value', 'started_at', 'ended_at', 'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function pet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function activityType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feedingPlanSlot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FeedingPlanSlot::class);
    }
}
