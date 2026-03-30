<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Pet extends Model
{
    protected $fillable = [
        'household_id', 'name', 'species', 'breed', 'birth_date', 'weight', 'avatar',
    ];

    protected $appends = ['avatar_url', 'quick_activity_type_ids'];

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

    /**
     * @return list<int>
     */
    public function getQuickActivityTypeIdsAttribute(): array
    {
        if (! $this->relationLoaded('quickActivityTypes')) {
            return [];
        }

        return $this->quickActivityTypes->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
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

    public function quickActivityTypes(): BelongsToMany
    {
        return $this->belongsToMany(ActivityType::class, 'pet_quick_action')->withTimestamps();
    }
}
