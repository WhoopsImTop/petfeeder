<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedingPlan extends Model
{
    protected $fillable = ['household_id', 'name'];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(FeedingPlanSlot::class);
    }

    public function pets(): BelongsToMany
    {
        return $this->belongsToMany(Pet::class, 'feeding_plan_pet')->withTimestamps();
    }
}
