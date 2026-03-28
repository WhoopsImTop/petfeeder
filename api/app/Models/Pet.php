<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Pet extends Model
{
    protected $fillable = [
        'household_id', 'name', 'species', 'breed', 'birth_date', 'weight', 'avatar',
    ];

    protected $appends = ['avatar_url'];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function getAvatarUrlAttribute(): ?string
    {
        $v = $this->attributes['avatar'] ?? null;
        if (empty($v)) {
            return null;
        }
        if (is_string($v) && str_starts_with($v, 'http')) {
            return $v;
        }

        return Storage::disk('public')->url($v);
    }

    public function household(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function feedingPlans(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(FeedingPlan::class, 'feeding_plan_pet')->withTimestamps();
    }
}
