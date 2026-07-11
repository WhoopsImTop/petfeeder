<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'feeder_webhook_token',
        'feeder_webhook_enabled',
        'feeder_action_open_activity_type_id',
        'feeder_action_stay_closed_activity_type_id',
        'feeder_action_none_activity_type_id',
    ];

    protected $casts = [
        'feeder_webhook_enabled' => 'boolean',
    ];

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'expires_at'])
            ->withTimestamps();
    }

    public function pets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pet::class);
    }

    public function activityTypes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityType::class);
    }

    public function feedingPlans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FeedingPlan::class);
    }

    public function pendingInvites(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HouseholdInvite::class)->whereNull('accepted_at');
    }

    public function feederEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FeederEvent::class);
    }
}
