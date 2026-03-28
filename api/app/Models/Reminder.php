<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'pet_id', 'activity_type_id', 'household_id', 'reminder_group_id', 'title', 'time', 'frequency', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function activityType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function household(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
