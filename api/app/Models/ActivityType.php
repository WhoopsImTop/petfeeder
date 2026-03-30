<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActivityType extends Model
{
    protected $fillable = [
        'household_id', 'name', 'type', 'icon', 'is_fast_action'
    ];

    protected $casts = [
        'is_fast_action' => 'boolean',
    ];

    public function household(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function petsWithQuickAction(): BelongsToMany
    {
        return $this->belongsToMany(Pet::class, 'pet_quick_action')->withTimestamps();
    }
}
