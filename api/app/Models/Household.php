<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    protected $fillable = ['name'];

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
}
